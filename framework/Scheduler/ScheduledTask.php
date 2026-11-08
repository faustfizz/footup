<?php

namespace Footup\Scheduler;

class ScheduledTask
{
    protected callable $callback;
    protected ?string $frequency = null;
    protected ?\DateTimeInterface $lastRun = null;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function everyMinute(): self
    {
        $this->frequency = 'everyMinute';

        return $this;
    }

    public function hourly(): self
    {
        $this->frequency = 'hourly';

        return $this;
    }

    public function daily(): self
    {
        $this->frequency = 'daily';

        return $this;
    }

    public function weekly(): self
    {
        $this->frequency = 'weekly';

        return $this;
    }

    public function monthly(): self
    {
        $this->frequency = 'monthly';

        return $this;
    }

    public function isDue(): bool
    {
        if (!$this->frequency) {
            return true;
        }

        $now = new \DateTime();
        $lastRun = $this->lastRun ?? new \DateTime('2000-01-01');

        return match ($this->frequency) {
            'everyMinute' => $now->getTimestamp() - $lastRun->getTimestamp() >= 60,
            'hourly' => $now->getTimestamp() - $lastRun->getTimestamp() >= 3600,
            'daily' => $now->format('Y-m-d') !== $lastRun->format('Y-m-d'),
            'weekly' => $now->format('W') !== $lastRun->format('W'),
            'monthly' => $now->format('Y-m') !== $lastRun->format('Y-m'),
            default => true,
        };
    }

    public function execute(): void
    {
        call_user_func($this->callback);
        $this->lastRun = new \DateTime();
    }
}