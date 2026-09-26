<?php

declare(strict_types=1);

namespace Footup\Http;

use Footup\Files\Mime;

/*
|--------------------------------------------------------------------------
| DownloadResponse — file download/streaming
|
| Usage:
|   return download('path/to/file.pdf');
|   return download('path/to/file.pdf', 'custom-name.pdf');
|   return download_stream('path/to/large-file.zip');
|--------------------------------------------------------------------------
*/

class DownloadResponse extends Response
{
    /**
     * Chémin du fichier
     */
    protected string $filePath = '';

    /**
     * Nom du fichier pour le téléchargement
     */
    protected string $fileName = '';

    /**
     * Type MIME
     */
    protected string $mimeType = 'application/octet-stream';

    /**
     * Inline (afficher) ou télécharger
     */
    protected bool $inline = false;

    /**
     * Taille du buffer en octets (streaming)
     */
    protected int $bufferSize = 8192;

    /**
     * Données brutes (contenu)
     */
    public mixed $content = '';

    /**
     * Ressource stream
     */
    protected mixed $stream = null;

    /**
     * Headers personnalisés
     */
    protected array $customHeaders = [];

    /**
     * Encodage de transfert
     */
    protected string $encoding = 'binary';

    /**
     * Compresser la réponse
     */
    protected bool $compress = false;

    /**
     * Taille du cache (ETag)
     */
    protected ?int $cacheTime = null;

    public function __construct()
    {
        parent::__construct('', 200);
        $this->status = 200;
    }
    
    /**
     * Créer une réponse pour afficher inline (dans le navigateur)
     */
    public static function inline(string $filePath, ?string $fileName = null): self
    {
        $instance = self::file($filePath, $fileName);
        $instance->inline = true;
        return $instance;
    }

    /**
     * Créer une réponse pour un fichier
     */
    public static function file(string $filePath, ?string $fileName = null): self
    {
        if (!file_exists($filePath)) {
            throw new \Footup\Exceptions\NotFoundHttpException("File not found: {$filePath}");
        }

        $instance = new self();
        $instance->filePath = $filePath;
        $instance->fileName = $fileName ?? basename($filePath);
        $instance->mimeType = self::detectMimeType($filePath);
        $instance->status = 200;

        return $instance;
    }

    /**
     * Créer une réponse pour un contenu
     */
    public static function content(string $content, string $fileName, string $mimeType = 'text/plain'): self
    {
        $instance = new self();
        $instance->content = $content;
        $instance->fileName = $fileName;
        $instance->mimeType = $mimeType;
        $instance->status = 200;

        return $instance;
    }

    /**
     * Créer une réponse pour un stream
     */
    public static function stream($stream, string $fileName, string $mimeType = 'application/octet-stream'): self
    {
        if (!is_resource($stream)) {
            throw new \RuntimeException("Unvalid stream resource provided for DownloadResponse.");
        }

        $instance = new self();
        $instance->stream = $stream;
        $instance->fileName = $fileName;
        $instance->mimeType = $mimeType;
        $instance->status = 200;

        return $instance;
    }

    /**
     * Créer une réponse depuis une URL distante
     */
    public static function fromUrl(string $url, ?string $fileName = null, array $options = []): self
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException("Invalid URL: {$url}");
        }

        // Options par défaut
        $defaults = [
            'timeout' => 30,
            'ssl_verify' => true,
            'user_agent' => 'Footup-DownloadResponse/1.0',
            'follow_redirects' => true,
            'headers' => [],
        ];

        $options = array_merge($defaults, $options);

        // Créer le contexte stream
        $contextOptions = [
            'http' => [
                'timeout' => $options['timeout'],
                'user_agent' => $options['user_agent'],
                'follow_location' => $options['follow_redirects'] ? 1 : 0,
                'max_redirects' => 5,
            ],
            'ssl' => [
                'verify_peer' => $options['ssl_verify'],
                'allow_self_signed' => !$options['ssl_verify'],
            ],
        ];

        // Ajouter les headers personnalisés
        if (!empty($options['headers'])) {
            $headers = [];
            foreach ($options['headers'] as $key => $value) {
                $headers[] = "{$key}: {$value}";
            }
            $contextOptions['http']['header'] = $headers;
        }

        $context = stream_context_create($contextOptions);

        // Télécharger le contenu
        $content = @file_get_contents($url, false, $context);
        if ($content === false) {
            throw new \RuntimeException("Cannot download from: {$url}");
        }

        // Déterminer le nom du fichier
        if (!$fileName) {
            $fileName = basename(parse_url($url, PHP_URL_PATH)) ?: 'download';
        }

        // Déterminer le MIME type depuis les headers HTTP si disponibles
        $mimeType = 'application/octet-stream';
        if (!empty($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (stripos($header, 'Content-Type:') === 0) {
                    $mimeType = trim(explode(';', substr($header, 13))[0]);
                    break;
                }
            }
        }

        // Créer une instance avec le contenu téléchargé
        $instance = new self();
        $instance->content = $content;
        $instance->fileName = $fileName;
        $instance->mimeType = $mimeType;
        $instance->status = 200;

        return $instance;
    }

    /**
     * Créer une réponse depuis une URL avec cache local
     */
    public static function downloadAndCache(
        string $url,
        string $cacheDir,
        string $fileName = null,
        array $options = []
    ): self {
        $defaults = [
            'cache_ttl' => 86400, // 24 heures
            'timeout' => 30,
            'ssl_verify' => true,
        ];

        $options = array_merge($defaults, $options);

        // Déterminer le nom du fichier en cache
        if (!$fileName) {
            $fileName = basename(parse_url($url, PHP_URL_PATH)) ?: md5($url);
        }

        $cacheFile = rtrim($cacheDir, '/') . '/' . $fileName;

        // Vérifier le cache
        if (file_exists($cacheFile)) {
            $fileAge = time() - filemtime($cacheFile);
            if ($fileAge < $options['cache_ttl']) {
                // Utiliser le fichier en cache
                return self::file($cacheFile, $fileName);
            }
        }

        // Créer le répertoire de cache s'il n'existe pas
        if (!is_dir($cacheDir)) {
            if (!@mkdir($cacheDir, 0755, true)) {
                throw new \RuntimeException("Cannot create cache dir: {$cacheDir}");
            }
        }

        // Télécharger le fichier
        $response = self::fromUrl($url, $fileName, $options);

        // Sauvegarder dans le cache
        if ($response->content !== null) {
            if (!@file_put_contents($cacheFile, $response->content)) {
                throw new \RuntimeException("Cannot save file to cache dir: {$cacheFile}");
            }
        }

        return $response;
    }

    /**
     * Renommer le fichier à télécharger
     */
    public function as(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * Setter le type MIME
     */
    public function mimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * Télécharger le fichier (par défaut)
     */
    public function download(): self
    {
        $this->inline = false;
        return $this;
    }

    /**
     * Setter la taille du buffer (streaming)
     */
    public function bufferSize(int $bytes): self
    {
        $this->bufferSize = max(1, $bytes);
        return $this;
    }

    /**
     * Ajouter un header personnalisé
     */
    public function header(string $key, string $value): self
    {
        $this->customHeaders[$key] = $value;
        return $this;
    }

    /**
     * Ajouter plusieurs headers
     */
    public function headers(array $headers): self
    {
        $this->customHeaders = array_merge($this->customHeaders, $headers);
        return $this;
    }

    /**
     * Compresser la réponse avec gzip
     */
    public function compress(bool $compress = true): self
    {
        $this->compress = $compress;
        return $this;
    }

    /**
     * Setter le temps de cache (ETag)
     */
    public function cache(int $seconds): self
    {
        $this->cacheTime = $seconds;
        return $this;
    }

    /**
     * Pas de cache (Cache-Control: no-cache)
     */
    public function noCache(): self
    {
        $this->cacheTime = 0;
        return $this;
    }

    /**
     * Obtenir le nom du fichier
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Obtenir le type MIME
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Obtenir la taille du fichier (en octets)
     */
    public function getSize(): ?int
    {
        if ($this->filePath && file_exists($this->filePath)) {
            return filesize($this->filePath);
        }

        if ($this->content !== null) {
            return strlen($this->content);
        }

        if ($this->stream) {
            $stat = fstat($this->stream);
            return $stat['size'] ?? null;
        }

        return null;
    }

    /**
     * Obtenir tous les headers HTTP (override parent)
     */
    public function getHeaders(): array
    {
        $headers = parent::getHeaders(); // Récupérer les headers du parent

        // Headers de contenu
        $headers['Content-Type'] = $this->mimeType;
        $headers['Content-Disposition'] = $this->getContentDisposition();
        $headers['Content-Transfer-Encoding'] = $this->encoding;

        // Headers de cache
        if ($this->cacheTime === 0) {
            $headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
            $headers['Pragma'] = 'no-cache';
            $headers['Expires'] = 'Thu, 01 Jan 1970 00:00:00 GMT';
        } else if ($this->cacheTime !== null) {
            $headers['Cache-Control'] = "max-age={$this->cacheTime}, public";
            $headers['Expires'] = gmdate('D, d M Y H:i:s T', time() + $this->cacheTime);
        } else {
            $headers['Cache-Control'] = 'private, no-cache';
        }

        // Headers de sécurité
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options'] = 'DENY';

        // Taille du fichier
        if ($size = $this->getSize()) {
            $headers['Content-Length'] = (string)$size;
        }

        // ETag
        if ($this->filePath && file_exists($this->filePath)) {
            $headers['ETag'] = '"' . md5_file($this->filePath) . '"';
        }

        // Last-Modified
        if ($this->filePath && file_exists($this->filePath)) {
            $mtime = filemtime($this->filePath);
            $headers['Last-Modified'] = gmdate('D, d M Y H:i:s T', $mtime);
        }

        // Compression
        if ($this->compress) {
            $headers['Content-Encoding'] = 'gzip';
        }

        // Ajouter les headers personnalisés
        $headers = array_merge($headers, $this->customHeaders);

        return $headers;
    }

    /**
     * Obtenir le header Content-Disposition
     */
    protected function getContentDisposition(): string
    {
        $disposition = $this->inline ? 'inline' : 'attachment';

        // RFC 5987 pour les noms Unicode
        $fileName = $this->sanitizeFileName($this->fileName);

        return "{$disposition}; filename=\"{$fileName}\"; filename*=UTF-8''{$fileName}";
    }

    /**
     * Nettoyer le nom du fichier (sécurité)
     */
    protected function sanitizeFileName(string $fileName): string
    {
        // Supprimer les chemins
        $fileName = basename($fileName);

        // Remplacer les caractères dangereux
        $fileName = preg_replace('/[^a-zA-Z0-9._\-]/', '', $fileName);

        // Limiter la longueur
        return substr($fileName, 0, 255);
    }

    /**
     * Formatter une taille en octets en format lisible
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Envoyer la réponse au client (override parent)
     */
    public function send(): void
    {
        // Vérifier que les headers n'ont pas déjà été envoyés
        if (headers_sent()) {
            throw new \RuntimeException("Headers already sent, cannot send download response.");
        }

        // Envoyer le code de statut
        http_response_code($this->status);

        // Envoyer les headers
        foreach ($this->getHeaders() as $key => $value) {
            header("{$key}: {$value}");
        }

        // Envoyer le contenu
        if ($this->filePath) {
            $this->sendFile();
        } else if ($this->content !== null) {
            $this->sendContent();
        } else if ($this->stream) {
            $this->sendStream();
        }
    }

    /**
     * Envoyer un fichier
     */
    protected function sendFile(): void
    {
        if ($this->compress) {
            $this->sendFileCompressed();
        } else {
            $this->sendFileWithReadfile();
        }
    }

    /**
     * Envoyer le fichier avec readfile() (plus efficace)
     */
    protected function sendFileWithReadfile(): void
    {
        // Désactiver la limite de temps pour les gros fichiers
        set_time_limit(0);

        readfile($this->filePath);
    }

    /**
     * Envoyer le fichier compressé
     */
    protected function sendFileCompressed(): void
    {
        set_time_limit(0);

        $handle = fopen($this->filePath, 'rb');
        if (!$handle) {
            throw new \RuntimeException("Cannot open the file: {$this->filePath}");
        }

        $compressor = gzopen('php://output', 'wb9');
        if (!$compressor) {
            fclose($handle);
            throw new \RuntimeException("Cannot initialize gzip compression");
        }

        try {
            while (!feof($handle)) {
                $chunk = fread($handle, $this->bufferSize);
                if ($chunk === false) {
                    break;
                }
                gzwrite($compressor, $chunk);
            }
        } finally {
            gzclose($compressor);
            fclose($handle);
        }
    }

    /**
     * Envoyer le contenu brut
     */
    protected function sendContent(): void
    {
        if ($this->compress) {
            echo gzencode($this->content, 9);
        } else {
            echo $this->content;
        }
    }

    /**
     * Envoyer un stream
     */
    protected function sendStream(): void
    {
        set_time_limit(0);

        if ($this->compress) {
            $compressor = gzopen('php://output', 'wb9');
            if (!$compressor) {
                throw new \RuntimeException("Cannot initialize gzip compression for stream");
            }

            while (!feof($this->stream)) {
                $chunk = fread($this->stream, $this->bufferSize);
                if ($chunk === false) {
                    break;
                }
                gzwrite($compressor, $chunk);
            }

            gzclose($compressor);
        } else {
            while (!feof($this->stream)) {
                $chunk = fread($this->stream, $this->bufferSize);
                if ($chunk === false) {
                    break;
                }
                echo $chunk;
            }
        }

        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    protected static function detectMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeType = Mime::getMime($extension);

        return $mimeType ?? 'application/octet-stream';
    }

    protected function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
    }
}
