<?php

declare(strict_types=1);

namespace Footup\Scheduler;

/*
|--------------------------------------------------------------------------
| Scheduler — register tasks to run at intervals (cron jobs)
|
| Usage in bootstrap.php:
|   $scheduler = new Scheduler();
|   $scheduler->command('backup')->daily();
|   $scheduler->call(fn() => cleanup())->hourly();
|
| Run: php bin/console.php schedule:run (in your crontab: * * * * * php /path/bin/console.php schedule:run)
|--------------------------------------------------------------------------
*/

class Scheduler
{
    protected array $tasks = [];

    /**
     * Schedule a CLI command
     * e.g. $scheduler->command('backup')->daily()
     */
    public function command(string $command): ScheduledTask
    {
        return $this->call(function () use ($command) {
            passthru("php footup {$command}");
        });
    }

    /**
     * Schedule a callable
     * e.g. $scheduler->call(fn() => User::cleanup())->hourly()
     */
    public function call(callable $callback): ScheduledTask
    {
        $task = new ScheduledTask($callback);
        $this->tasks[] = $task;

        return $task;
    }

    /**
     * Run all tasks that are due
     */
    public function run(): void
    {
        foreach ($this->tasks as $task) {
            if ($task->isDue()) {
                $task->execute();
            }
        }
    }

    /**
     * Get all scheduled tasks
     */
    public function tasks(): array
    {
        return $this->tasks;
    }
}
