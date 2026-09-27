<?php

namespace Footup\Url;

class Url
{
    /**
     * URL complète
     */
    protected string $url;

    /**
     * Composants parsés
     */
    protected array $parts;

    /**
     * Query parameters
     */
    protected array $query = [];

    /**
     * Fragment
     */
    protected string $fragment = '';

    /**
     * Constructeur
     */
    public function __construct(string $url = '')
    {
        $this->url = $url;
        $this->parse();
    }

    /**
     * Crée une nouvelle instance
     */
    public static function make(string $url = ''): static
    {
        return new static($url);
    }

    /**
     * Parse l'URL
     */
    protected function parse(): void
    {
        if (empty($this->url)) {
            $this->parts = [];
            return;
        }

        $this->parts = parse_url($this->url) ?: [];

        if (!empty($this->parts['query'])) {
            parse_str($this->parts['query'], $this->query);
        }

        if (!empty($this->parts['fragment'])) {
            $this->fragment = $this->parts['fragment'];
        }
    }

    /**
     * Définit le schéma
     */
    public function scheme(string $scheme = null): self|string
    {
        if ($scheme === null) {
            return $this->parts['scheme'] ?? '';
        }

        $this->parts['scheme'] = $scheme;
        return $this;
    }

    /**
     * Définit l'hôte
     */
    public function host(string $host = null): self|string
    {
        if ($host === null) {
            return $this->parts['host'] ?? '';
        }

        $this->parts['host'] = $host;
        return $this;
    }

    /**
     * Définit le port
     */
    public function port(int $port = null): self|int|null
    {
        if ($port === null) {
            return $this->parts['port'] ?? null;
        }

        $this->parts['port'] = $port;
        return $this;
    }

    /**
     * Définit le chemin
     */
    public function path(string $path = null): self|string
    {
        if ($path === null) {
            return $this->parts['path'] ?? '';
        }

        $path = '/' . ltrim($path, '/');
        $this->parts['path'] = $path;
        return $this;
    }

    /**
     * Obtient les segments du chemin
     */
    public function segments(): array
    {
        $path = $this->path();
        return array_filter(explode('/', $path));
    }

    /**
     * Obtient un segment spécifique (1-indexed)
     */
    public function segment(int $index): ?string
    {
        $segments = $this->segments();
        return $segments[$index - 1] ?? null;
    }

    /**
     * Ajoute un segment au chemin
     */
    public function addSegment(string ...$segments): self
    {
        $path = rtrim($this->path(), '/');
        
        foreach ($segments as $segment) {
            $segment = trim($segment, '/');
            if (!empty($segment)) {
                $path .= '/' . $segment;
            }
        }

        $this->parts['path'] = $path;
        return $this;
    }

    /**
     * Remplace le dernier segment
     */
    public function replaceSegment(string $segment): self
    {
        $path = $this->path();
        $segments = $this->segments();

        if (!empty($segments)) {
            array_pop($segments);
        }

        $segments[] = trim($segment, '/');
        $newPath = '/' . implode('/', array_filter($segments));
        $this->parts['path'] = $newPath;

        return $this;
    }

    /**
     * Ajoute un paramètre de query
     */
    public function withQuery(string $key, mixed $value = null): self
    {
        if (is_array($key)) {
            $this->query = array_merge($this->query, $key);
        } else {
            $this->query[$key] = $value;
        }

        return $this;
    }

    /**
     * Obtient un paramètre de query
     */
    public function getQuery(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Obtient tous les paramètres de query
     */
    public function allQuery(): array
    {
        return $this->query;
    }

    /**
     * Retire un paramètre de query
     */
    public function withoutQuery(string ...$keys): self
    {
        foreach ($keys as $key) {
            unset($this->query[$key]);
        }

        return $this;
    }

    /**
     * Remplace les paramètres de query
     */
    public function replaceQuery(array $query): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Définit le fragment
     */
    public function fragment(string $fragment = null): self|string
    {
        if ($fragment === null) {
            return $this->fragment;
        }

        $this->fragment = ltrim($fragment, '#');
        return $this;
    }

    /**
     * Obtient l'autorité (host + port)
     */
    public function authority(): string
    {
        $authority = $this->host();

        if ($this->port()) {
            $authority .= ':' . $this->port();
        }

        return $authority;
    }

    /**
     * Obtient la base URL (scheme + authority)
     */
    public function base(): string
    {
        $base = $this->scheme() . '://' . $this->authority();
        return $base;
    }

    /**
     * Construit l'URL complète
     */
    public function build(): string
    {
        $url = '';

        if ($this->scheme()) {
            $url .= $this->scheme() . '://';
        }

        if ($this->authority()) {
            $url .= $this->authority();
        }

        $url .= $this->path();

        if (!empty($this->query)) {
            $url .= '?' . http_build_query($this->query);
        }

        if (!empty($this->fragment)) {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }

    /**
     * Alias pour build()
     */
    public function __toString(): string
    {
        return $this->build();
    }

    /**
     * Crée une URL relative
     */
    public function relative(): string
    {
        $url = $this->path();

        if (!empty($this->query)) {
            $url .= '?' . http_build_query($this->query);
        }

        if (!empty($this->fragment)) {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }

    /**
     * Vérifie si l'URL est absolue
     */
    public function isAbsolute(): bool
    {
        return !empty($this->parts['scheme']);
    }

    /**
     * Compare deux URLs
     */
    public function equals(string|self $other): bool
    {
        if ($other instanceof self) {
            return $this->build() === $other->build();
        }

        return $this->build() === $other;
    }

    /**
     * Obtient le chemin avec query string (relative)
     */
    public function pathWithQuery(): string
    {
        return $this->path() . 
               (!empty($this->query) ? '?' . http_build_query($this->query) : '');
    }

    /**
     * Crée une URL avec une base donnée
     */
    public static function of(string $base, string $path = ''): static
    {
        $url = new static($base);
        if ($path) {
            $url->addSegment($path);
        }
        return $url;
    }

    /**
     * Retourne les éléments parsés
     */
    public function getParts(): array
    {
        return $this->parts;
    }
}
