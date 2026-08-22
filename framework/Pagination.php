<?php

declare(strict_types=1);

namespace Footup;

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
    public function __construct(
        public int $currentPage = 1,
        public int $perPage = 15
    ) {
    }

    public static function fromRequest(?int $defaultPerPage = 15): self
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));

        return new self($page, $defaultPerPage);
    }

    public function offset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }
}
