<?php

namespace Footup\Paginator;

class Paginator extends AbstractPaginator
{
    /**
     * Default value for query string key to specify the current page.
     *
     * https://example.com?page=1
     */
    const DEFAULT_PAGE_NAME = 'page';

    /**
     * Default value for the number of visible pages around the chosen page in case there are a lot of pages.
     */
    const DEFAULT_ON_EACH_SIDE = 3;

    /**
     * @var string
     */
    private $pageName;

    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $onEachSide;

    /**
     * @var \App\Config\Paginator
     */
    private $config;

    /**
     * Paginator constructor.
     *
     * @param $totalItems
     * @param int    $perPage
     * @param int    $currentPageNumber
     * @param string $url
     */
    public function __construct(
        $totalItems,
        $perPage = null,
        $currentPageNumber = 1,
        $url = '',
        \App\Config\Paginator|null $config = null
    ) {
        $this->config = !is_null($config) ? $config : new \App\Config\Paginator();
        if(!is_null($perPage) && is_int($perPage))
        {
            $this->config->perPage = $perPage;
        }
        foreach($this->config as $key => $val)
        {
            $this->{$key} = $val;
        }

        parent::__construct($totalItems, $this->perPage ?? self::DEFAULT_PER_PAGE, $currentPageNumber);
        $this->setUrl($url);
    }

    /**
     * @throws PaginatorException
     *
     * @return null|Page
     */
    public function getNextPage(): ?Page
    {
        $currentPage = $this->getCurrentPage();
        if ($this->isOnLastPage() === true || !$currentPage instanceof Page) {
            return null;
        }

        return $this->createPageObject($currentPage->getNumber() + 1);
    }

    /**
     * @throws PaginatorException
     *
     * @return null|Page
     */
    public function getPreviousPage(): ?Page
    {
        $currentPage = $this->getCurrentPage();
        if ($this->isOnFirstPage() === true || !$currentPage instanceof Page) {
            return null;
        }

        return $this->createPageObject($currentPage->getNumber() - 1);
    }

    /**
     * @return bool
     */
    public function isOnFirstPage(): bool
    {
        if ($this->getCurrentPage() instanceof Page && $this->getCurrentPage()->getNumber() === 1) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isOnLastPage(): bool
    {
        if ($this->getCurrentPage() instanceof Page &&
            $this->getCurrentPage()->getNumber() === $this->calculateNumberOfPages()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @throws PaginatorException
     *
     * @return null|string
     */
    public function getNextPageUrl(): ?string
    {
        $nextPage = $this->getNextPage();

        if (!$nextPage instanceof Page) {
            return null;
        }

        return $this->appendQueryStringToURL($this->getUrl(), [$this->getPageName() => $nextPage->getNumber()]);
    }

    /**
     * @throws PaginatorException
     *
     * @return null|string
     */
    public function getPreviousPageUrl(): ?string
    {
        $previousPage = $this->getPreviousPage();

        if (!$previousPage instanceof Page) {
            return null;
        }

        return $this->appendQueryStringToURL($this->getUrl(), [$this->getPageName() => $previousPage->getNumber()]);
    }

    /**
     * @return null|string
     */
    public function getFirstPageUrl(): ?string
    {
        return $this->appendQueryStringToURL($this->getUrl(), [$this->getPageName() => 1]);
    }

    /**
     * @return null|string
     */
    public function getLastPageUrl(): ?string
    {
        return $this->appendQueryStringToURL($this->getUrl(), [$this->getPageName() => ((int) ceil($this->getTotalItems() / $this->getPerPage()))]);
    }

    /**
     * @param string $url
     * @param string|array $query
     *
     * @return string
     */
    private function appendQueryStringToURL(string $url, array $query): string
    {
        $parsedUrl = parse_url($url);
        if (empty($parsedUrl['path'])) {
            $url .= '/';
        }

        $queryString = http_build_query($query);

        // check if there is already any query string in the URL
        $queryKey = 'query';
        if (empty($parsedUrl[$queryKey])) {
            // remove duplications
            parse_str($queryString, $queryStringArray);

            return $url.'?'.http_build_query($queryStringArray);
        }

        $queryString = $parsedUrl[$queryKey].'&'.$queryString;

        // remove duplications
        parse_str($queryString, $queryStringArray);

        // place the updated query in the original query position
        return substr_replace(
            $url,
            http_build_query($queryStringArray),
            strpos($url, $parsedUrl[$queryKey]),
            strlen($parsedUrl[$queryKey])
        );
    }

    /**
     * @return string
     */
    public function getPageName(): string
    {
        if (empty($this->pageName)) {
            return self::DEFAULT_PAGE_NAME;
        }

        return $this->pageName;
    }

    /**
     * @param string $pageName
     */
    public function setPageName(string $pageName): void
    {
        $this->pageName = $pageName;
    }

    /**
     * @param $number
     *
     * @throws PaginatorException
     *
     * @return Page
     */
    protected function createPageObject($number): Page
    {
        $page = new Page($number);
        $number === 1 ? $page->setIsFirst(true) : $page->setIsFirst(false);
        $number === $this->calculateNumberOfPages() ? $page->setIsLast(true) : $page->setIsLast(false);
        $this->getCurrentPage() instanceof Page && $number === $this->getCurrentPage()->getNumber() ?
            $page->setIsCurrent(true) : $page->setIsCurrent(false);

        if (!empty($this->getUrl())) {
            $page->setUrl(
                $this->appendQueryStringToURL($this->getUrl(), [$this->getPageName() => $number])
            );
        }

        $this->isPageWithinHiddenRange($page->getNumber()) ? $page->setIsHidden(true) : $page->setIsHidden(false);

        return $page;
    }

    /**
     * @return bool
     */
    private function isSliderCloseToBeginning(): bool
    {
        if ($this->getCurrentPage() instanceof Page &&
            $this->getCurrentPage()->getNumber() <= (2 * $this->getOnEachSide())) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isSliderCloseToEnding(): bool
    {
        if ($this->getCurrentPage() instanceof Page &&
            $this->getCurrentPage()->getNumber() > ($this->calculateNumberOfPages() - (2 * $this->getOnEachSide()))) {
            return true;
        }

        return false;
    }

    /**
     * @param $number
     *
     * @return bool
     */
    private function isPageWithinHiddenRange($number): bool
    {
        $hiddenRanges = $this->getHiddenRanges();
        foreach ($hiddenRanges as $hiddenRange) {
            if ($number > $hiddenRange['start'] && $number < $hiddenRange['finish']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getHiddenRanges(): array
    {
        if ($this->calculateNumberOfPages() < ($this->getOnEachSide() * 2) + 6) {
            return [];
        }

        if (!$this->getCurrentPage() instanceof Page || $this->isSliderCloseToBeginning() === true) {
            return [
                ['start'  => (2 * $this->getOnEachSide()) + 3, 'finish' => $this->calculateNumberOfPages() - 2],
            ];
        } elseif ($this->isSliderCloseToEnding() === true) {
            return [
                ['start'  => 3, 'finish' => $this->calculateNumberOfPages() - ((2 * $this->getOnEachSide()) + 3)],
            ];
        }

        return [
            [
                'start'  => 3,
                'finish' => $this->getCurrentPage()->getNumber() - ($this->getOnEachSide() + 1),
            ],
            [
                'start'  => $this->getCurrentPage()->getNumber() + ($this->getOnEachSide() + 1),
                'finish' => $this->calculateNumberOfPages() - 2,
            ],
        ];
    }

    /**
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * @return int
     */
    public function getOnEachSide(): int
    {
        if (!isset($this->onEachSide)) {
            return self::DEFAULT_ON_EACH_SIDE;
        }

        return (int) $this->onEachSide;
    }

    /**
     * @param int $onEachSide
     */
    public function setOnEachSide($onEachSide): void
    {
        $this->onEachSide = $onEachSide;
    }

    /**
     * @param $pageNumber
     *
     * @return int
     */
    public function calculateDatabaseOffset($pageNumber): int
    {
        $pageNumber = (int) $pageNumber;

        if ($pageNumber > 0) {
            return ($pageNumber - 1) * $this->getPerPage();
        }

        return 0;
    }

    public function displayLinks($pageName = self::DEFAULT_PAGE_NAME, $template = "default"): string
    {
        if (! in_array($template, $this->config->templates))
		{
			throw new PaginatorException(text("paginator.templateNotFound", [$template]));
		}
        $output = (function (string $view): string {
			extract(["paginator"    =>  $this]);
			ob_start();
			eval('?>' . $view);
			return ob_get_clean() ?: '';
		})(file_get_contents(__DIR__.'/View/'. $template .'.php'));

        return $output;
    }
}
