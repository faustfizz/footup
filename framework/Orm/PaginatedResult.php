<?php

declare(strict_types=1);

namespace Footup\Orm;

use Footup\Orm\Pagination;

/**
 * Paginated Query Result
 *
 * Wraps query results with pagination metadata.
 * Provides easy access to data and pagination state.
 *
 * Usage:
 *   $results = User::query()->paginate(15);
 *   $users = $results->data();
 *   $pagination = $results->pagination();
 *   echo "Page {$pagination->currentPage} of {$pagination->lastPage()}";
 */
class PaginatedResult implements \JsonSerializable
{
    public function __construct(
        private array $data,
        private Pagination $pagination
    ) {
    }

    /**
     * Get all data rows
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Get first data row
     */
    public function first(): ?object
    {
        return $this->data[0] ?? null;
    }

    /**
     * Get last data row
     */
    public function last(): ?object
    {
        $count = count($this->data);
        return $count > 0 ? $this->data[$count - 1] : null;
    }

    /**
     * Check if result set is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Get pagination object
     */
    public function pagination(): Pagination
    {
        return $this->pagination;
    }

    /**
     * Get total results count
     */
    public function total(): int
    {
        return $this->pagination->totalResults();
    }

    /**
     * Get results per page
     */
    public function perPage(): int
    {
        return $this->pagination->perPage;
    }

    /**
     * Get current page number
     */
    public function currentPage(): int
    {
        return $this->pagination->currentPage;
    }

    /**
     * Get last page number
     */
    public function lastPage(): int
    {
        return $this->pagination->lastPage();
    }

    /**
     * Check if there's a next page
     */
    public function hasMore(): bool
    {
        return $this->pagination->hasMore();
    }

    /**
     * Check if there's a previous page
     */
    public function hasPrevious(): bool
    {
        return $this->pagination->hasPrevious();
    }

    /**
     * Check if current page is the first
     */
    public function isFirstPage(): bool
    {
        return $this->pagination->isFirstPage();
    }

    /**
     * Check if current page is the last
     */
    public function isLastPage(): bool
    {
        return $this->pagination->isLastPage();
    }

    /**
     * Get first result number (1-indexed)
     */
    public function from(): int
    {
        return $this->pagination->from();
    }

    /**
     * Get last result number (1-indexed)
     */
    public function to(): int
    {
        return $this->pagination->to();
    }

    /**
     * Get next page number
     */
    public function nextPage(): ?int
    {
        return $this->pagination->nextPage();
    }

    /**
     * Get previous page number
     */
    public function previousPage(): ?int
    {
        return $this->pagination->previousPage();
    }

    /**
     * Get all page numbers as array
     * Usage: {foreach $results->links() as $page}...{/foreach}
     */
    public function links(): array
    {
        return $this->pagination->links();
    }

    /**
     * Check if pagination is valid
     */
    public function isValid(): bool
    {
        return $this->pagination->isValid();
    }

    /**
     * Convert to array (JSON API friendly)
     */
    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'pagination' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'from' => $this->from(),
                'to' => $this->to(),
                'has_more' => $this->hasMore(),
            ],
        ];
    }

    /**
     * Convert to array (JSON API friendly)
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
