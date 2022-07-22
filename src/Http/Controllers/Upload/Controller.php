<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Upload;

use Aws\S3\S3Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

class Controller extends BaseController
{
    public string $path = 'tmp';

    public function forLocal(Request $request): JsonResponse
    {
        $filename = (string) Str::uuid();
        $filename .= '.' . $request->input('extension');

        $prefix = $request->input('prefix');
        $route = $prefix ? ltrim($prefix, '/') . '.upload.files' : 'upload.files';

        $url = URL::temporarySignedRoute($route, now()->addMinutes(5), ['file' => $filename]);

        return response()->json([
            'key' => $filename,
            'disk' => 'local',
            'headers' => [
                'Content-Type' => $request->input('content_type') ?: 'application/octet-stream',
            ],
            'attributes' => [
                'action' => $url,
                'name' => $filename,
                'extension' => $request->input('extension'),
            ],
            'inputs' => $request->input(),
        ], 201);
    }

    public function upload(Request $request): JsonResponse
    {
        abort_unless($request->hasValidSignature(), 401);

        $files = $request->allFiles();
        $path = $this->path;

        $disk = config('filesystems.default');
        $filename = $request->input('file');

        $fileHashPaths = collect($files)->map(function ($file) use ($disk, $path, $filename): string {
            return $file->storeAs($path, $filename, $disk);
        });

        // Strip out the temporary upload directory from the paths.
        $paths = $fileHashPaths->map(function ($name) use ($path) {
            return str_replace($path . '/', '', $name);
        });

        return response()->json([
            'paths' => $paths,
        ]);
    }

    public function revert(Request $request): array
    {
        $disk = config('filesystems.default');
        $path = $this->path . '/';
        $file = $request->input('filename');
        $disk = Storage::disk($disk);

        if ($disk->exists($path . $file)) {
            $disk->delete($path . $file);
        }

        return [
            'status' => 'OK',
        ];
    }

    /**
     * Create a new signed URL.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signed(Request $request)
    {
        $disk = config('filesystems.default');
        if ('local' == $disk) {
            return $this->forLocal($request);
        }

        return $this->forS3($request);
    }

    public function forS3(Request $request): JsonResponse
    {
        $disk = config('filesystems.cloud');
        $filename = (string) Str::uuid();
        $filename .= '.' . $request->input('extension');

        $path = $this->path . '/';

        $driver = Storage::disk($disk);
        $adapter = $driver->getAdapter();
        $client = $adapter->getClient();

        $command = $this->createCommand($request, $client, $adapter, $path . $filename);
        $signedRequest = $client->createPresignedRequest($command, '+5 minutes');

        $uri = $signedRequest->getUri();

        return response()->json([
            'key' => $filename,
            'disk' => 's3',
            'headers' => $this->headers($request, $signedRequest),
            'attributes' => [
                'action' => (string) $uri,
                'name' => $filename,
                'extension' => $request->input('extension'),
            ],
            'inputs' => [],
        ], 201);
    }

    /**
     * Create a command for the PUT operation.
     *
     * @param string $key
     *
     * @return \Aws\CommandInterface
     */
    protected function createCommand(Request $request, S3Client $client, AwsS3Adapter $adapter, $key)
    {
        return $client->getCommand('putObject', array_filter([
            'Bucket' => $adapter->getBucket(),
            'Key' => $adapter->getPathPrefix() . $key,
            'ACL' => $request->input('visibility') ?: $this->defaultVisibility(),
            'ContentType' => $request->input('content_type') ?: 'application/octet-stream',
            'CacheControl' => $request->input('cache_control') ?: null,
            'Expires' => $request->input('expires') ?: null,
        ]));
    }

    /**
     * Get the headers that should be used when making the signed request.
     *
     * @param mixed $signedRequest
     *
     * @return array
     */
    protected function headers(Request $request, $signedRequest)
    {
        return array_merge(
            $signedRequest->getHeaders(),
            [
                'Content-Type' => $request->input('content_type') ?: 'application/octet-stream',
            ]
        );
    }

    /**
     * Get the default visibility for uploads.
     *
     * @return string
     */
    protected function defaultVisibility()
    {
        return 'private';
    }
}
