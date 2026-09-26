<?php

declare(strict_types=1);

namespace Footup\Http;

use Footup\Utils\ImageCropper;

/*
|--------------------------------------------------------------------------
| Base64 — Convert, validate, and upload base64 encoded data
|
| Supports encoding/decoding files and handling data URLs
|
| Usage:
|   // Decode and save base64 file
|   $file = Base64::decode($base64String, 'uploads');
|   
|   // Decode data URL (data:image/jpeg;base64,...)
|   $file = Base64::fromDataUrl($dataUrl, 'avatars');
|   
|   // Encode existing file to base64
|   $base64 = Base64::encode('/path/to/file.jpg');
|
|   // Crop image in base64 and save
|   $file = Base64::fromDataUrl($dataUrl, 'avatars')
|       ->cropper()->crop(0, 0, 200, 200)->save('/thumb.jpg');
|--------------------------------------------------------------------------
*/

class Base64
{
    private ?UploadedFile $file = null;
    private string $mime = '';
    private string $originalName = '';

    private function __construct(
        private string $data,
        private string $directory = 'uploads'
    ) {
    }

    /**
     * Create from base64 string
     * 
     * @param string $base64 Raw base64 data (without data URL prefix)
     * @param string $directory Storage directory
     * @param string $filename Optional filename (auto-generated if null)
     * @return self
     */
    public static function decode(
        string $base64,
        string $directory = 'uploads',
        ?string $filename = null
    ): self {
        $instance = new self($base64, $directory);
        $instance->originalName = $filename ?? 'file_' . uniqid();
        return $instance;
    }

    /**
     * Create from data URL
     * Extracts MIME type and data from data:image/jpeg;base64,... format
     *
     * @param string $dataUrl Data URL (data:image/jpeg;base64,ABC123...)
     * @param string $directory Storage directory
     * @return self
     */
    public static function fromDataUrl(string $dataUrl, string $directory = 'uploads'): self
    {
        // Parse data URL
        if (!preg_match('/^data:([^;]+);base64,(.+)$/', $dataUrl, $matches)) {
            throw new \Exception('Invalid data URL format');
        }

        $mime = $matches[1];
        $base64 = $matches[2];

        $instance = new self($base64, $directory);
        $instance->mime = $mime;
        
        // Extract extension from MIME type
        $ext = self::extensionFromMime($mime);
        $instance->originalName = 'file_' . uniqid() . '.' . $ext;

        return $instance;
    }

    /**
     * Create from file path
     * Reads file and encodes to base64
     *
     * @param string $filePath Path to file
     * @return string Base64 encoded data
     */
    public static function encode(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $data = file_get_contents($filePath);
        if ($data === false) {
            throw new \Exception("Failed to read file: {$filePath}");
        }

        return base64_encode($data);
    }

    /**
     * Create data URL from file path
     *
     * @param string $filePath Path to file
     * @return string Data URL (data:image/jpeg;base64,...)
     */
    public static function toDataUrl(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $mime = mime_content_type($filePath);
        $data = file_get_contents($filePath);

        if ($data === false) {
            throw new \Exception("Failed to read file: {$filePath}");
        }

        $base64 = base64_encode($data);
        return "data:{$mime};base64,{$base64}";
    }

    /**
     * Get MIME type
     */
    public function mime(): string
    {
        return $this->mime;
    }

    /**
     * Get file size (of decoded data in bytes)
     */
    public function size(): int
    {
        // Base64 decoded size is roughly 3/4 of encoded size
        $decoded = @base64_decode($this->data, true);
        return $decoded ? strlen($decoded) : 0;
    }

    /**
     * Set MIME type explicitly
     */
    public function setMime(string $mime): self
    {
        $this->mime = $mime;
        return $this;
    }

    /**
     * Set original filename
     */
    public function setFilename(string $filename): self
    {
        $this->originalName = $filename;
        return $this;
    }

    /**
     * Validate base64 data
     */
    public function isValid(): bool
    {
        // Check if it's valid base64
        if (@base64_decode($this->data, true) === false) {
            return false;
        }

        return true;
    }

    /**
     * Validate file size
     */
    public function validateSize(int $maxBytes): bool
    {
        return $this->size() <= $maxBytes;
    }

    /**
     * Validate MIME type
     */
    public function validateMime(array $allowedMimes): bool
    {
        if (empty($this->mime)) {
            return false;
        }

        return in_array($this->mime, $allowedMimes);
    }

    /**
     * Save to file
     * 
     * @param string|null $filename Custom filename (uses originalName if null)
     * @return string Storage path (relative to storage)
     */
    public function save(?string $filename = null): string
    {
        if (!$this->isValid()) {
            throw new \Exception('Invalid base64 data');
        }

        $decoded = base64_decode($this->data, true);
        if ($decoded === false) {
            throw new \Exception('Failed to decode base64 data');
        }

        $dir = storage_path($this->directory);
        @mkdir($dir, 0775, true);

        $filename = $filename ?? $this->originalName;
        $path = $dir . '/' . $filename;

        if (!file_put_contents($path, $decoded)) {
            throw new \Exception("Failed to save file to: {$path}");
        }

        return $this->directory . '/' . $filename;
    }

    /**
     * Save as image with optional processing
     * 
     * @param string|null $filename Custom filename
     * @return array ['path' => '...', 'mime' => '...']
     */
    public function saveImage(?string $filename = null): array
    {
        $path = $this->save($filename);

        return [
            'path' => $path,
            'mime' => $this->mime,
            'size' => filesize(storage_path($path)),
        ];
    }

    /**
     * Save image with thumbnail
     * 
     * @param int $thumbWidth Thumbnail width
     * @param int $thumbHeight Thumbnail height
     * @param int $quality JPEG quality
     * @return array ['original' => '...', 'thumbnail' => '...']
     */
    public function saveImageWithThumbnail(
        int $thumbWidth = 300,
        int $thumbHeight = 300,
        int $quality = 85
    ): array {
        // Save original
        $originalPath = $this->save();
        $fullPath = storage_path($originalPath);

        // Create thumbnail
        $thumbDir = storage_path($this->directory . '/thumbnails');
        @mkdir($thumbDir, 0775, true);

        $thumbFilename = uniqid() . '.' . pathinfo($this->originalName, PATHINFO_EXTENSION);
        $thumbPath = $thumbDir . '/' . $thumbFilename;
        $thumbStoragePath = $this->directory . '/thumbnails/' . $thumbFilename;

        // Use ImageCropper to create thumbnail
        $cropper = new ImageCropper($fullPath);
        $cropper
            ->thumbnail($thumbWidth, $thumbHeight)
            ->save($thumbPath, $quality);

        return [
            'original' => $originalPath,
            'thumbnail' => $thumbStoragePath,
        ];
    }

    /**
     * Save multiple thumbnail sizes
     * 
     * @param array $sizes Thumbnail sizes ['thumb' => [150, 150], 'small' => [300, 300]]
     * @param int $quality JPEG quality
     * @return array Multi-level array with all thumbnails
     */
    public function saveImageMultiThumbnails(
        array $sizes = [],
        int $quality = 85
    ): array {
        // Default sizes if not specified
        $defaultSizes = [
            'thumb' => [150, 150],
            'small' => [300, 300],
            'medium' => [600, 600],
            'large' => [1200, 1200],
        ];

        $sizes = array_merge($defaultSizes, $sizes);

        // Save original
        $originalPath = $this->save();
        $fullPath = storage_path($originalPath);

        $result = [
            'original' => $originalPath,
            'thumbnails' => [],
        ];

        // Create each thumbnail
        foreach ($sizes as $name => [$width, $height]) {
            $thumbDir = storage_path($this->directory . '/' . $name);
            @mkdir($thumbDir, 0775, true);

            $filename = uniqid() . '.' . pathinfo($this->originalName, PATHINFO_EXTENSION);
            $thumbPath = $thumbDir . '/' . $filename;
            $storagePath = $this->directory . '/' . $name . '/' . $filename;

            $cropper = new ImageCropper($fullPath);
            $cropper
                ->thumbnail($width, $height)
                ->save($thumbPath, $quality);

            $result['thumbnails'][$name] = $storagePath;
        }

        return $result;
    }

    /**
     * Get ImageCropper for fluent image manipulation
     * Requires file to be saved first
     */
    public function cropper(): ImageCropper
    {
        if (!$this->isValid()) {
            throw new \Exception('Cannot create cropper from invalid base64 data');
        }

        if (empty($this->mime) || strpos($this->mime, 'image/') === false) {
            throw new \Exception('Base64 data is not an image');
        }

        // Temporarily save and return cropper
        $path = $this->save();
        return new ImageCropper(storage_path($path));
    }

    /**
     * Get as data URL
     */
    public function getDataUrl(?string $mime = null): string
    {
        $mime = $mime ?? $this->mime ?? 'application/octet-stream';
        return "data:{$mime};base64,{$this->data}";
    }

    /**
     * Get base64 string
     */
    public function raw(): string
    {
        return $this->data;
    }

    /**
     * Helper: Get extension from MIME type
     */
    private static function extensionFromMime(string $mime): string
    {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'application/json' => 'json',
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar',
        ];

        return $mimeMap[$mime] ?? 'bin';
    }

    /**
     * Helper: Get MIME type from extension
     */
    public static function mimeFromExtension(string $ext): string
    {
        $extMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'json' => 'application/json',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
        ];

        return $extMap[strtolower($ext)] ?? 'application/octet-stream';
    }
}
