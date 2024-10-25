<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Upload;

use Aws\CommandInterface;
use Aws\S3\S3Client;
use Diviky\Bright\Rules\FileValidationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

class Controller extends BaseController
{
    public string $path = 'tmp';

    public function forLocal(Request $request): JsonResponse
    {
        $extension = $request->post('extension');
        $filename = $request->post('filename', (string) Str::uuid());

        if ($extension) {
            $filename .= '.' . $extension;
        }

        $disk = 'local';
        $path = $request->input('folder', $this->path . '/');

        $prefix = $request->post('prefix');
        $route = $prefix ? ltrim($prefix, '/') . '.upload.files' : 'upload.files';

        $url = URL::temporarySignedRoute($route, now()->addMinutes(30), ['file' => $filename]);

        $mimes = MimeTypes::getDefault()->getMimeTypes($extension);
        $content_type = $request->post('content_type') ?: ($mimes ? $mimes[0] : null);

        return response()->json([
            'key' => $filename,
            'url' => Storage::disk($disk)->temporaryUrl($path . $filename, now()->addMinutes(10)),
            'file' => $path . $filename,
            'disk' => $disk,
            'headers' => [
                'Content-Type' => $content_type ?: 'application/octet-stream',
            ],
            'attributes' => [
                'action' => $url,
                'method' => 'POST',
                'name' => $filename,
                'extension' => $request->post('extension'),
                'accept' => $request->post('accept'),
            ],
            'inputs' => [
                'accept' => $request->post('accept'),
                'extension' => $request->post('extension'),
            ],
        ], 201);
    }

    public function upload(FileValidationRequest $request): JsonResponse
    {
        abort_unless($request->hasValidSignature(), 401);

        $files = $request->allFiles();
        $path = $request->input('folder', $this->path);

        $disk = config('filesystems.default');
        $filename = $request->input('file');

        $fileHashPaths = collect($files)->map(function ($file) use ($disk, $path, $filename): string {
            return $filename ? $file->storeAs($path, $filename, $disk) : $file->store($path, $disk);
        });

        $paths = [];
        foreach ($fileHashPaths as $name) {
            $paths[] = [
                'file' => $name,
                'name' => str_replace($path . '/', '', $name),
                'folder' => $path,
                'url' => Storage::disk($disk)->temporaryUrl($name, now()->addMinutes(10)),
            ];
        }

        return response()->json([
            'paths' => $paths,
        ]);
    }

    public function store(FileValidationRequest $request): JsonResponse
    {
        $files = $request->allFiles();
        $path = $request->input('folder', '/');

        $disk = config('filesystems.default');
        $filename = $request->input('file');

        $fileHashPaths = collect($files)->map(function ($file) use ($disk, $path, $filename): string {
            return $filename ? $file->storeAs($path, $filename, $disk) : $file->store($path, $disk);
        });

        $paths = [];
        foreach ($fileHashPaths as $name) {
            $paths[] = [
                'file' => $name,
                'name' => str_replace($path . '/', '', $name),
                'folder' => $path,
                'url' => Storage::disk($disk)->temporaryUrl($name, now()->addMinutes(10)),
            ];
        }

        return response()->json([
            'paths' => $paths,
        ]);
    }

    public function revert(Request $request): array
    {
        $disk = config('filesystems.default');
        $file = $request->input('filename');
        $disk = Storage::disk($disk);
        $path = $request->input('folder', $this->path . '/');

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
     * @return JsonResponse
     */
    public function signed(Request $request)
    {
        $disk = config('filesystems.default');
        if ($disk == 'local' || $disk == 'public') {
            return $this->forLocal($request);
        }

        return $this->forS3($request);
    }

    public function forS3(Request $request): JsonResponse
    {
        $config = config('filesystems.disks.s3');
        $disk = 's3';

        $extension = $request->post('extension');
        $filename = $request->post('filename', (string) Str::uuid());

        if ($extension) {
            $filename .= '.' . $extension;
        }

        $path = $request->input('folder', $this->path . '/');
        $client = $this->storageClient();

        $command = $this->createCommand($request, $client, $config['bucket'], $path . $filename);
        $signedRequest = $client->createPresignedRequest($command, '+30 minutes');

        $uri = $signedRequest->getUri();

        $extension = $request->post('extension');
        $mimes = MimeTypes::getDefault()->getMimeTypes($extension);
        $content_type = $request->post('content_type') ?: ($mimes ? $mimes[0] : null);

        return response()->json([
            'key' => $filename,
            'url' => Storage::disk($disk)->temporaryUrl($path . $filename, now()->addMinutes(30)),
            'file' => $path . $filename,
            'disk' => $disk,
            'headers' => array_merge($signedRequest->getHeaders(), [
                'Content-Type' => $content_type ?: 'application/octet-stream',
            ]),
            'attributes' => [
                'action' => (string) $uri,
                'method' => 'PUT',
                'name' => $filename,
                'extension' => $request->post('extension'),
            ],
            'inputs' => [],
        ], 201);
    }

    /**
     * Create a command for the PUT operation.
     *
     * @return CommandInterface
     */
    protected function createCommand(Request $request, S3Client $client, string $bucket, string $key)
    {
        $extension = $request->input('extension');
        $mimes = MimeTypes::getDefault()->getMimeTypes($extension);
        $content_type = $request->input('content_type') ?: ($mimes ? $mimes[0] : null);

        return $client->getCommand('putObject', array_filter([
            'Bucket' => $bucket,
            'Key' => $key,
            'ACL' => $request->input('visibility') ?: $this->defaultVisibility(),
            'ContentType' => $content_type ?: 'application/octet-stream',
            'CacheControl' => $request->input('cache_control') ?: null,
            'Expires' => $request->input('expires') ?: null,
        ]));
    }

    /**
     * Get the S3 storage client instance.
     *
     * @return S3Client
     */
    protected function storageClient()
    {
        $config = [
            'region' => config('filesystems.disks.s3.region', $_ENV['AWS_DEFAULT_REGION'] ?? null),
            'version' => 'latest',
            'signature_version' => 'v4',
            'use_path_style_endpoint' => config('filesystems.disks.s3.use_path_style_endpoint', false),
        ];

        if (!isset($_ENV['AWS_LAMBDA_FUNCTION_VERSION'])) {
            $config['credentials'] = array_filter([
                'key' => config('filesystems.disks.s3.key', $_ENV['AWS_ACCESS_KEY_ID'] ?? null),
                'secret' => config('filesystems.disks.s3.secret', $_ENV['AWS_SECRET_ACCESS_KEY'] ?? null),
                'token' => config('filesystems.disks.s3.token', $_ENV['AWS_SESSION_TOKEN'] ?? null),
            ]);

            if (!empty($_ENV['AWS_URL'])) {
                $config['url'] = $_ENV['AWS_URL'];
                $config['endpoint'] = $_ENV['AWS_URL'];
            }
        }

        return new S3Client($config);
    }

    /**
     * Ensure the required environment variables are available.
     */
    protected function ensureEnvironmentVariablesAreAvailable(Request $request): void
    {
        $missing = array_diff_key(array_flip(array_filter([
            $request->input('bucket') ? null : 'AWS_BUCKET',
            'AWS_DEFAULT_REGION',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
        ])), $_ENV);

        if (empty($missing)) {
            return;
        }

        throw new \InvalidArgumentException(
            'Unable to issue signed URL. Missing environment variables: ' . implode(', ', array_keys($missing))
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
