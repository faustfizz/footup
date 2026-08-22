<?php

declare(strict_types=1);

namespace Footup\Http;

/*
|--------------------------------------------------------------------------
| UploadedFile — handle $_FILES with validation and storage
|
| Usage:
|   $file = uploaded_file('avatar');
|   if ($file && $file->size() <= 2_000_000) {
|       $path = $file->store('avatars');
|   }
|--------------------------------------------------------------------------
*/

class UploadedFile
{
    public function __construct(protected array $file)
    {
    }

    public function name(): string
    {
        return $this->file['name'] ?? '';
    }

    public function mimeType(): string
    {
        return $this->file['type'] ?? '';
    }

    public function size(): int
    {
        return (int) ($this->file['size'] ?? 0);
    }

    public function path(): string
    {
        return $this->file['tmp_name'] ?? '';
    }

    public function error(): int
    {
        return (int) ($this->file['error'] ?? UPLOAD_ERR_NO_FILE);
    }

    public function isValid(): bool
    {
        return $this->error() === UPLOAD_ERR_OK && is_uploaded_file($this->path());
    }

    public function extension(): string
    {
        return pathinfo($this->name(), PATHINFO_EXTENSION);
    }

    public function validate(int $maxSize = null, array $allowedMimes = null): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        if ($maxSize && $this->size() > $maxSize) {
            return false;
        }

        if ($allowedMimes && ! in_array($this->mimeType(), $allowedMimes)) {
            return false;
        }

        return true;
    }

    public function store(string $directory = 'uploads', string $disk = null): ?string
    {
        if (! $this->isValid()) {
            return null;
        }

        $dir = storage_path($directory);
        @mkdir($dir, 0775, true);

        $filename = uniqid() . '.' . $this->extension();
        $path = $dir . '/' . $filename;

        if (move_uploaded_file($this->path(), $path)) {
            return $directory . '/' . $filename;
        }

        return null;
    }
}
