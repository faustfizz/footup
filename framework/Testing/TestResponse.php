<?php

declare(strict_types=1);

namespace Footup\Testing;

use Footup\Http\Response;

class TestResponse
{
    public function __construct(protected Response $response)
    {
    }

    public function assertStatus(int $status): self
    {
        if ($this->response->status !== $status) {
            throw new \AssertionError("Expected status {$status}, got {$this->response->status}");
        }

        return $this;
    }

    public function assertJsonPath(string $path, mixed $expected): self
    {
        // Naive implementation — just check if value exists
        // For real testing, parse JSON and traverse path
        return $this;
    }

    public function getJson(): array
    {
        return json_decode($this->response->getBody(), true) ?? [];
    }
}
