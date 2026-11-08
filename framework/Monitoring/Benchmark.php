<?php

declare(strict_types=1);

namespace Footup\Monitoring;

/*
|--------------------------------------------------------------------------
| Benchmark
|--------------------------------------------------------------------------
| A tiny labeled stopwatch. View::render() (View.php) and
| Connection::statement() (Connection.php) already report into this
| automatically, so out of the box you get per-view and per-query timing
| for free. Use it directly for anything else you want measured:
|
|   Benchmark::measure('report.generate', fn () => $this->buildReport());
|
| TimingMiddleware (app/bootstrap.php) turns all of this into a
| Server-Timing response header, visible in the browser's Network tab
| with zero extra tooling. Reset per-request or marks accumulate for
| the life of the PHP process (relevant under php-fpm / `php -S`).
*/

class Benchmark
{
    /** @var array<string, float> label => start microtime() */
    protected static array $starts = [];

    /** @var array<string, float[]> label => list of elapsed ms */
    protected static array $marks = [];

    public static function start(string $label): void
    {
        self::$starts[$label] = microtime(true);
    }

    /** Stops the timer for $label and records + returns the elapsed milliseconds. */
    public static function stop(string $label): float
    {
        $elapsed = (microtime(true) - (self::$starts[$label] ?? microtime(true))) * 1000;

        self::$marks[$label][] = $elapsed;
        unset(self::$starts[$label]);

        return $elapsed;
    }

    /** Times a callback under $label and returns the callback's own result. */
    public static function measure(string $label, \Closure $callback): mixed
    {
        self::start($label);

        try {
            return $callback();
        } finally {
            self::stop($label);
        }
    }

    /** @return array<string, float[]> raw elapsed-ms lists, keyed by label */
    public static function all(): array
    {
        return self::$marks;
    }

    /** @return array<string, array{count:int, total_ms:float, avg_ms:float, min_ms:float, max_ms:float}> */
    public static function summary(): array
    {
        $summary = [];

        foreach (self::$marks as $label => $times) {
            $summary[$label] = [
                'count' => count($times),
                'total_ms' => round(array_sum($times), 3),
                'avg_ms' => round(array_sum($times) / count($times), 3),
                'min_ms' => round(min($times), 3),
                'max_ms' => round(max($times), 3),
            ];
        }

        return $summary;
    }

    public static function reset(): void
    {
        self::$starts = [];
        self::$marks = [];
    }
}
