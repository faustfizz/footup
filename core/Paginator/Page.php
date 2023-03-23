<?php

namespace Footup\Paginator;

class Page
{
    /**
     * @var int
     */
    private $number;

    /**
     * @var bool
     */
    private $isFirst;

    /**
     * @var bool
     */
    private $isLast;

    /**
     * @var bool
     */
    private $isCurrent;

    /**
     * @var bool
     */
    private $isHidden;

    /**
     * @var string
     */
    private $url;

    /**
     * Page constructor.
     *
     * @param $number
     *
     * @throws PaginatorException
     */
    public function __construct(int $number)
    {
        $this->setNumber($number);
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param $number
     *
     * @throws PaginatorException
     */
    public function setNumber(int $number): void
    {
        if ($this->isPageNumberValid($number) !== true) {
            throw new PaginatorException(text("paginator.invalidNumber", [$number]));
        }

        $this->number = $number;
    }

    /**
     * @param $number
     *
     * @return bool
     */
    private function isPageNumberValid($number): bool
    {
        return $number > 0;
    }

    /**
     * @return bool
     */
    public function isFirst(): bool
    {
        return (bool) $this->isFirst;
    }

    /**
     * @param bool $isFirst
     */
    public function setIsFirst($isFirst): void
    {
        $this->isFirst = $isFirst;
    }

    /**
     * @return bool
     */
    public function isLast(): bool
    {
        return (bool) $this->isLast;
    }

    /**
     * @param bool $isLast
     */
    public function setIsLast($isLast): void
    {
        $this->isLast = $isLast;
    }

    /**
     * @return bool
     */
    public function isCurrent()
    {
        return (bool) $this->isCurrent;
    }

    /**
     * @param bool $isCurrent
     */
    public function setIsCurrent($isCurrent): void
    {
        $this->isCurrent = $isCurrent;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return (bool) $this->isHidden;
    }

    /**
     * @param bool $isHidden
     */
    public function setIsHidden($isHidden): void
    {
        $this->isHidden = $isHidden;
    }

    /**
     * @return null|string
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
}
