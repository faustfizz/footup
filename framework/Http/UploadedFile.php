<?php

declare(strict_types=1);

namespace Footup\Http;

use Footup\Utils\Support\ImageCropper;

/*
|--------------------------------------------------------------------------
| UploadedFile — handle $_FILES with validation, storage, and image cropping
|
| Usage:
|   $file = uploaded_file('avatar');
|   if ($file && $file->size() <= 2_000_000) {
|       $path = $file->store('avatars');
|   }
|
| Image cropping:
|   $file = uploaded_file('photo');
|   // Crop and save thumbnail
|   $file->cropSave(
|       'avatars',
|       x: 50, y: 50, width: 200, height: 200,
|       thumbWidth: 150, thumbHeight: 150
|   );
|
|   // Or manual control with ImageCropper
|   $file->crop(10, 10, 300, 300)->resize(150, 150)->save('thumbs/avatar.jpg');
|--------------------------------------------------------------------------
*/

class UploadedFile
{
    private ?ImageCropper $cropper = null;

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

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        $imageMimes = [
            'image/jpeg', 'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
        ];

        return in_array($this->mimeType(), $imageMimes);
    }

    /**
     * Get ImageCropper instance for this file
     * Allows fluent API for image manipulation
     */
    public function cropper(): ImageCropper
    {
        if (!$this->isValid()) {
            throw new \Exception("Cannot create ImageCropper from invalid file");
        }

        if (!$this->isImage()) {
            throw new \Exception("File is not an image: {$this->mimeType()}");
        }

        if ($this->cropper === null) {
            $this->cropper = new ImageCropper($this->path());
        }

        return $this->cropper;
    }

    /**
     * Crop image and save
     * Returns both original and thumbnail paths
     *
     * @param string $directory Directory to save files
     * @param int $x X coordinate for crop
     * @param int $y Y coordinate for crop
     * @param int $width Crop width
     * @param int $height Crop height
     * @param int|null $thumbWidth Thumbnail width (null = skip thumbnail)
     * @param int|null $thumbHeight Thumbnail height (null = skip thumbnail)
     * @param int $quality JPEG quality 1-100
     * @return array ['original' => 'path/to/original.jpg', 'thumbnail' => 'path/to/thumb.jpg']
     */
    public function cropSave(
        string $directory = 'uploads',
        int $x = 0,
        int $y = 0,
        int $width = null,
        int $height = null,
        ?int $thumbWidth = null,
        ?int $thumbHeight = null,
        int $quality = 85
    ): array {
        if (!$this->isValid()) {
            throw new \Exception("Cannot crop invalid file");
        }

        if (!$this->isImage()) {
            throw new \Exception("File is not an image");
        }

        // Use full dimensions if not specified
        $cropper = $this->cropper();
        $width = $width ?? $cropper->width();
        $height = $height ?? $cropper->height();

        // Store original uploaded file
        $originalPath = $this->store($directory);

        if (!$originalPath) {
            throw new \Exception("Failed to store original file");
        }

        // Create thumbnail if dimensions specified
        if ($thumbWidth && $thumbHeight) {
            $thumbDir = storage_path($directory . '/thumbnails');
            @mkdir($thumbDir, 0775, true);

            $thumbFilename = uniqid() . '.' . $this->extension();
            $thumbPath = $thumbDir . '/' . $thumbFilename;
            $thumbStoragePath = $directory . '/thumbnails/' . $thumbFilename;

            // Reload cropper for fresh copy
            $thumbCropper = new ImageCropper(storage_path($originalPath));
            $thumbCropper
                ->crop($x, $y, $width, $height)
                ->thumbnail($thumbWidth, $thumbHeight)
                ->save($thumbPath, $quality);

            return [
                'original' => $originalPath,
                'thumbnail' => $thumbStoragePath,
            ];
        }

        return [
            'original' => $originalPath,
            'thumbnail' => null,
        ];
    }

    /**
     * Create thumbnail from image file
     * Saves both original and thumbnail
     *
     * @param string $directory Storage directory
     * @param int $thumbWidth Thumbnail width
     * @param int $thumbHeight Thumbnail height
     * @param int $quality JPEG quality
     * @return array ['original' => '...', 'thumbnail' => '...']
     */
    public function thumbnail(
        string $directory = 'uploads',
        int $thumbWidth = 300,
        int $thumbHeight = 300,
        int $quality = 85
    ): array {
        return $this->cropSave(
            $directory,
            x: 0,
            y: 0,
            thumbWidth: $thumbWidth,
            thumbHeight: $thumbHeight,
            quality: $quality
        );
    }

    /**
     * Save multiple thumbnail sizes
     *
     * @param string $directory Storage directory
     * @param array $sizes Array of ['small' => [200, 200], 'medium' => [400, 400], 'large' => [800, 800]]
     * @param int $quality JPEG quality
     * @return array Multi-level array with paths for each size
     */
    public function makeThumbnails(
        string $directory = 'uploads',
        array $sizes = [],
        int $quality = 85
    ): array {
        if (!$this->isValid() || !$this->isImage()) {
            throw new \Exception("Invalid image file");
        }

        $defaultSizes = [
            'thumb' => [150, 150],
            'small' => [300, 300],
            'medium' => [600, 600],
            'large' => [1200, 1200],
        ];

        $sizes = array_merge($defaultSizes, $sizes);

        // Store original
        $originalPath = $this->store($directory);

        $result = [
            'original' => $originalPath,
            'thumbnails' => [],
        ];

        // Create each thumbnail size
        foreach ($sizes as $name => [$width, $height]) {
            $thumbDir = storage_path($directory . '/' . $name);
            @mkdir($thumbDir, 0775, true);

            $filename = uniqid() . '.' . $this->extension();
            $thumbPath = $thumbDir . '/' . $filename;
            $storagePath = $directory . '/' . $name . '/' . $filename;

            $cropper = new ImageCropper(storage_path($originalPath));
            $cropper
                ->thumbnail($width, $height)
                ->save($thumbPath, $quality);

            $result['thumbnails'][$name] = $storagePath;
        }

        return $result;
    }
}
