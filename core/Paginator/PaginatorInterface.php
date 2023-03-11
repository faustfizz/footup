<?php

namespace Footup\Paginator;

interface PaginatorInterface
{
    /**
     * @return int
     */
    public function getTotalItems(): int;

    /**
     * @param $totalItems
     */
    public function setTotalItems($totalItems): void;

    /**
     * @return int
     */
    public function getPerPage(): int;

    /**
     * @param $perPage
     */
    public function setPerPage($perPage): void;

    /**
     * @param $pageNumber
     */
    public function setCurrentPage($pageNumber): void;

    /**
     * @return mixed
     */
    public function getCurrentPage();

    /**
     * @return int
     */
    public function calculateNumberOfPages(): int;

    /**
     * @return bool
     */
    public function hasPages(): bool;

    /**
     * @return Page[]
     */
    public function getPages();

    /**
     * @param ?string $pageName
     * @param ?string $template
     * @return string
     */
    public function displayLinks($pageName, $template): string;
}
