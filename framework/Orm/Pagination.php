<?php

declare(strict_types=1);

namespace Footup\Orm;

/*
|--------------------------------------------------------------------------
| Pagination — simple result paginator
|
| Usage:
|   $page = Pagination::fromRequest();
|   $users = User::paginate(15, $page);
|   // returns ['data' => [...], 'total' => 150, 'per_page' => 15, 'current_page' => 1]
|--------------------------------------------------------------------------
*/

class Pagination
{
    public int $currentPage;
    public int $perPage;
    public int $total = 0;
    private int $lastPage = 1;

    public function __construct(int $currentPage = 1, int $perPage = 15, int $total = 0)
    {
        $this->currentPage = max(1, $currentPage);
        $this->perPage = max(1, min(100, $perPage));  // 1-100 range
        $this->total = max(0, $total);
        $this->lastPage = $this->calculateLastPage();
    }

    public static function fromRequest(?int $defaultPerPage = 15): self
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = max(1, min(100, $defaultPerPage ?? 15));

        return new self($page, $perPage);
    }

    public function offset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    /**
     * Set total results and recalculate last page
     */
    public function setTotal(int $total): self
    {
        $this->total = max(0, $total);
        $this->lastPage = $this->calculateLastPage();
        return $this;
    }

    /**
     * Set last page (for custom pagination)
     */
    public function setLastPage(int $lastPage): self
    {
        $this->lastPage = max(1, $lastPage);
        return $this;
    }

    /**
     * Calculate last page based on total and perPage
     */
    private function calculateLastPage(): int
    {
        if ($this->total === 0) {
            return 1;
        }
        return (int) ceil($this->total / $this->perPage);
    }

    public function lastPage(): int
    {
        return $this->lastPage;
    }

    public function totalResults(): int
    {
        return $this->total;
    }

    public function hasMore(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    public function isFirstPage(): bool
    {
        return $this->currentPage === 1;
    }

    public function isLastPage(): bool
    {
        return $this->currentPage === $this->lastPage;
    }

    public function nextPage(): ?int
    {
        return $this->hasMore() ? $this->currentPage + 1 : null;
    }

    public function previousPage(): ?int
    {
        return $this->hasPrevious() ? $this->currentPage - 1 : null;
    }

    /**
     * First result number (1-indexed)
     */
    public function from(): int
    {
        if ($this->total === 0) {
            return 0;
        }
        return $this->offset() + 1;
    }

    /**
     * Last result number (1-indexed)
     */
    public function to(): int
    {
        if ($this->total === 0) {
            return 0;
        }
        return min($this->offset() + $this->perPage, $this->total);
    }

    /**
     * Array of page numbers to display (simplified: just page numbers)
     */
    public function links(): array
    {
        $pages = [];
        for ($i = 1; $i <= $this->lastPage; $i++) {
            $pages[] = $i;
        }
        return $pages;
    }

    /**
     * Validate if current page is valid
     */
    public function isValid(): bool
    {
        return $this->currentPage >= 1 && 
               ($this->total === 0 || $this->currentPage <= $this->lastPage);
    }
}
