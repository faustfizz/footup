<?php

declare(strict_types=1);

namespace Footup;

/*
|--------------------------------------------------------------------------
| ImageCropper — Crop and manipulate images, generate thumbnails
|
| Supports: JPG, PNG, GIF, WebP
| Requires: GD library (php-gd)
|
| Usage:
|   $cropper = new ImageCropper('/path/to/image.jpg');
|   $cropper->crop(0, 0, 200, 200)->save('/path/to/thumb.jpg');
|
|   // Or with fluent API
|   ImageCropper::load('/path/to/image.jpg')
|       ->crop(x: 10, y: 10, width: 100, height: 100)
|       ->resize(width: 150, height: 150)
|       ->save('/output.jpg', quality: 85);
|
|   // Generate thumbnail
|   ImageCropper::load('/image.jpg')
|       ->thumbnail(width: 300, height: 300)
|       ->save('/thumb.jpg');
|--------------------------------------------------------------------------
*/

class ImageCropper
{
    private mixed $image = null;
    private string $mime = '';
    private int $width = 0;
    private int $height = 0;

    public function __construct(private string $imagePath)
    {
        if (!file_exists($imagePath)) {
            throw new \Exception("Image file not found: {$imagePath}");
        }

        if (!extension_loaded('gd')) {
            throw new \Exception("GD library is not installed. Install php-gd to use ImageCropper.");
        }

        $this->loadImage();
    }

    /**
     * Factory method - static constructor
     */
    public static function load(string $imagePath): self
    {
        return new self($imagePath);
    }

    /**
     * Load image from file into memory
     */
    private function loadImage(): void
    {
        $imageInfo = @getimagesize($this->imagePath);

        if ($imageInfo === false) {
            throw new \Exception("Unable to read image file: {$this->imagePath}");
        }

        $this->width = $imageInfo[0];
        $this->height = $imageInfo[1];
        $this->mime = $imageInfo['mime'] ?? 'image/jpeg';

        // Load image based on MIME type
        $this->image = match ($this->mime) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($this->imagePath),
            'image/png' => imagecreatefrompng($this->imagePath),
            'image/gif' => imagecreatefromgif($this->imagePath),
            'image/webp' => imagecreatefromwebp($this->imagePath),
            default => throw new \Exception("Unsupported image format: {$this->mime}"),
        };

        if ($this->image === false) {
            throw new \Exception("Failed to load image: {$this->imagePath}");
        }
    }

    /**
     * Get image width
     */
    public function width(): int
    {
        return $this->width;
    }

    /**
     * Get image height
     */
    public function height(): int
    {
        return $this->height;
    }

    /**
     * Get image MIME type
     */
    public function mime(): string
    {
        return $this->mime;
    }

    /**
     * Crop image to specified dimensions
     *
     * @param int $x X coordinate to start crop
     * @param int $y Y coordinate to start crop
     * @param int $width Width of crop area
     * @param int $height Height of crop area
     */
    public function crop(int $x, int $y, int $width, int $height): self
    {
        // Validate bounds
        $x = max(0, min($x, $this->width - 1));
        $y = max(0, min($y, $this->height - 1));
        $width = min($width, $this->width - $x);
        $height = min($height, $this->height - $y);

        if ($width <= 0 || $height <= 0) {
            throw new \Exception("Invalid crop dimensions: {$width}x{$height}");
        }

        // Create cropped image
        $cropped = imagecreatetruecolor($width, $height);

        // Preserve transparency for PNG and GIF
        if ($this->mime === 'image/png' || $this->mime === 'image/gif') {
            $transparent = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
            imagefill($cropped, 0, 0, $transparent);
            imagesavealpha($cropped, true);
        }

        imagecopy($cropped, $this->image, 0, 0, $x, $y, $width, $height);

        imagedestroy($this->image);
        $this->image = $cropped;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Crop to aspect ratio (e.g., 16:9, 1:1, 4:3)
     * Centers the crop if image is larger than requested ratio
     *
     * @param float $ratio Width/height ratio (e.g., 1.0 for square, 1.777 for 16:9)
     */
    public function cropToRatio(float $ratio): self
    {
        $currentRatio = $this->width / $this->height;

        if ($currentRatio > $ratio) {
            // Image is wider than desired ratio - crop width
            $newWidth = (int)($this->height * $ratio);
            $x = (int)(($this->width - $newWidth) / 2);
            $y = 0;
            return $this->crop($x, $y, $newWidth, $this->height);
        } else {
            // Image is taller than desired ratio - crop height
            $newHeight = (int)($this->width / $ratio);
            $x = 0;
            $y = (int)(($this->height - $newHeight) / 2);
            return $this->crop($x, $y, $this->width, $newHeight);
        }
    }

    /**
     * Resize image to new dimensions
     *
     * @param int|null $width New width (null to maintain aspect ratio)
     * @param int|null $height New height (null to maintain aspect ratio)
     * @param bool $maintainRatio Keep aspect ratio when resizing
     */
    public function resize(?int $width = null, ?int $height = null, bool $maintainRatio = true): self
    {
        if ($width === null && $height === null) {
            return $this;
        }

        if ($maintainRatio) {
            if ($width !== null && $height === null) {
                $height = (int)(($width / $this->width) * $this->height);
            } elseif ($height !== null && $width === null) {
                $width = (int)(($height / $this->height) * $this->width);
            } elseif ($width !== null && $height !== null) {
                // Both specified - resize to fit largest dimension while maintaining ratio
                $aspectRatio = $this->width / $this->height;
                $targetRatio = $width / $height;

                if ($aspectRatio > $targetRatio) {
                    $height = (int)($width / $aspectRatio);
                } else {
                    $width = (int)($height * $aspectRatio);
                }
            }
        }

        $resized = imagecreatetruecolor($width, $height);

        // Preserve transparency
        if ($this->mime === 'image/png' || $this->mime === 'image/gif') {
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
            imagesavealpha($resized, true);
        }

        imagecopyresampled(
            $resized, $this->image,
            0, 0, 0, 0,
            $width, $height,
            $this->width, $this->height
        );

        imagedestroy($this->image);
        $this->image = $resized;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Create thumbnail - crop to aspect ratio then resize
     *
     * @param int $width Thumbnail width
     * @param int $height Thumbnail height
     * @param bool $centerCrop Crop from center (true) or from top-left (false)
     */
    public function thumbnail(int $width, int $height, bool $centerCrop = true): self
    {
        if ($centerCrop) {
            // Crop to aspect ratio, then resize
            $ratio = $width / $height;
            $this->cropToRatio($ratio);
        }

        return $this->resize($width, $height, maintainRatio: false);
    }

    /**
     * Rotate image by degrees (90, 180, 270)
     */
    public function rotate(int $degrees): self
    {
        // Normalize degrees
        $degrees = $degrees % 360;

        if ($degrees === 0) {
            return $this;
        }

        // Only support 90, 180, 270
        if (!in_array($degrees, [90, 180, 270])) {
            throw new \Exception("Only 90°, 180°, 270° rotations are supported");
        }

        $angle = match ($degrees) {
            90 => 270,   // imagerotate uses counter-clockwise
            180 => 180,
            270 => 90,
        };

        $rotated = imagerotate($this->image, $angle, 0);

        if ($rotated === false) {
            throw new \Exception("Failed to rotate image");
        }

        imagedestroy($this->image);
        $this->image = $rotated;

        // Swap dimensions if rotating 90 or 270
        if ($degrees === 90 || $degrees === 270) {
            [$this->width, $this->height] = [$this->height, $this->width];
        }

        return $this;
    }

    /**
     * Save image to file
     *
     * @param string $outputPath Path to save image
     * @param int $quality Quality (1-100) for JPEG, default 85
     * @param string|null $format Output format (jpg, png, gif, webp). If null, uses input format
     */
    public function save(string $outputPath, int $quality = 85, ?string $format = null): string
    {
        @mkdir(dirname($outputPath), 0775, true);

        // Determine output format
        $format = strtolower($format ?? pathinfo($outputPath, PATHINFO_EXTENSION) ?? 'jpg');

        $success = match ($format) {
            'jpg', 'jpeg' => imagejpeg($this->image, $outputPath, $quality),
            'png' => imagepng($this->image, $outputPath),
            'gif' => imagegif($this->image, $outputPath),
            'webp' => imagewebp($this->image, $outputPath, $quality),
            default => throw new \Exception("Unsupported output format: {$format}"),
        };

        if (!$success) {
            throw new \Exception("Failed to save image to: {$outputPath}");
        }

        return $outputPath;
    }

    /**
     * Get image data as base64
     * Useful for displaying in HTML without saving
     */
    public function toDataUrl(string $format = 'jpg', int $quality = 85): string
    {
        ob_start();

        match ($format) {
            'jpg', 'jpeg' => imagejpeg($this->image, null, $quality),
            'png' => imagepng($this->image),
            'gif' => imagegif($this->image),
            'webp' => imagewebp($this->image, null, $quality),
            default => throw new \Exception("Unsupported format: {$format}"),
        };

        $imageData = ob_get_clean();
        $base64 = base64_encode($imageData);

        $mimeType = match ($format) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        };

        return "data:{$mimeType};base64,{$base64}";
    }

    /**
     * Clean up - destroy image resource
     */
    public function __destruct()
    {
        if (is_resource($this->image) || ($this->image instanceof \GdImage)) {
            imagedestroy($this->image);
        }
    }
}
