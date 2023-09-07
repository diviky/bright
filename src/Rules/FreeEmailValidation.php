<?php

declare(strict_types=1);

namespace Diviky\Bright\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Filesystem\Filesystem;

class FreeEmailValidation implements ValidationRule
{
    /**
     * The filesystem implementation.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new rule instance.
     */
    public function __construct(Filesystem $files = null)
    {
        $this->files = $files ?? new Filesystem();
    }

    /**
     * Determine if the validation rule passes.
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $parts = \explode('@', $value);
        $email = $parts[1];

        $path = \storage_path('app/tmp/free_email_provider_domains.txt');
        if ($this->files->exists($path)) {
            $json = \file_get_contents($path);
        } else {
            $url = 'https://gist.githubusercontent.com/tbrianjones/5992856/raw/93213efb652749e226e69884d6c048e595c1280a/free_email_provider_domains.txt';
            $json = \file_get_contents($url);
            $this->files->put($path, $json);
        }

        if (\preg_match("/{$email}/i", $json)) {
            $fail('The :attribute must be Business email');
        }
    }
}
