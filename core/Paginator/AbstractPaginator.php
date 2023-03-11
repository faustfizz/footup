<?php

namespace Footup\Paginator;

abstract class AbstractPaginator implements PaginatorInterface
{
    const DEFAULT_PER_PAGE = 10;

    /**
     * @var int
     */
    private $totalItems;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @var Page
     */
    private $currentPage;

    /**
     * AbstractPaginator constructor.
     *
     * @param     $totalItems
     * @param int $perPage
     * @param int $currentPageNumber
     */
    public function __construct(
        $totalItems,
        $perPage = self::DEFAULT_PER_PAGE,
        $currentPageNumber = 1
    ) {
        // do not use setter here because of updateCurrentPage()
        $this->totalItems = (int) $totalItems;
        $this->perPage = (int) $perPage;

        $this->setCurrentPage($currentPageNumber);
    }

    /**
     * @param $pageNumber
     *
     * @return mixed
     */
    abstract protected function createPageObject($pageNumber);

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * @param $totalItems
     */
    public function setTotalItems($totalItems): void
    {
        $this->totalItems = (int) $totalItems;

        // after changing totalItems, the current page is changed
        $this->updateCurrentPage();
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @param $perPage
     */
    public function setPerPage($perPage): void
    {
        $this->perPage = (int) $perPage;

        // after changing perPage, the current page is changed
        $this->updateCurrentPage();
    }

    /**
     * Update current page.
     */
    private function updateCurrentPage(): void
    {
        $pageNumber = 1;
        if ($this->getCurrentPage() instanceof Page) {
            $pageNumber = $this->getCurrentPage()->getNumber();
        }

        $this->setCurrentPage($pageNumber);
    }

    /**
     * @return Page
     */
    public function getCurrentPage(): ?Page
    {
        return $this->currentPage;
    }

    /**
     * @param $pageNumber
     */
    public function setCurrentPage($pageNumber): void
    {
        // by default set the current page to false. It might be overwritten later this function
        $this->currentPage = null;

        $pageNumber = (int) $pageNumber;
        if ($this->calculateNumberOfPages() > 0 &&
            $this->getPerPage() > 0 &&
            $pageNumber > 0 &&
            $pageNumber <= $this->calculateNumberOfPages()
        ) {
            $this->currentPage = $this->createPageObject($pageNumber);
        }
    }

    /**
     * @return int
     */
    public function calculateNumberOfPages(): int
    {
        $totalRecords = $this->getTotalItems();
        $pageSize = $this->getPerPage();

        if ($totalRecords === 0 || $pageSize === 0) {
            return 0;
        }

        $numberOfPages = 1;
        if ($totalRecords > $pageSize) {
            $numberOfPages = ceil($totalRecords / $pageSize);
        }

        return $numberOfPages;
    }

    /**
     * @return bool
     */
    public function hasPages(): bool
    {
        if ($this->calculateNumberOfPages() > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return Page[]
     */
    public function getPages()
    {
        $pages = [];
        for ($i = 1; $i <= $this->calculateNumberOfPages(); $i++) {
            $pages[$i] = $this->createPageObject($i);
        }

        return $pages;
    }
}
