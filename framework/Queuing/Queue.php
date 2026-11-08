<?php

declare(strict_types=1);

namespace Footup\Queuing;

/*
|--------------------------------------------------------------------------
| Queue — manage job queues with driver support
|
| Drivers: sync, database
| Configuration: QUEUE_DRIVER env variable
| Usage:
|   Queue::push(SendEmailJob::class, ['email' => 'user@example.com']);
|   // then: php bin/console.php queue:work
|--------------------------------------------------------------------------
*/

class Queue
{
    private static ?string $driver = null;

    /**
     * Get configured queue driver (from QUEUE_DRIVER env)
     */
    private static function driver(): string
    {
        if (self::$driver === null) {
            self::$driver = env('QUEUE_DRIVER', 'sync');
        }
        return self::$driver;
    }

    public static function push(string $jobClass, array $data = []): void
    {
        $driver = self::driver();

        match ($driver) {
            'sync' => self::executeSyncJob($jobClass, $data),
            'database' => self::pushDatabase($jobClass, $data),
            default => throw new \RuntimeException("Queue driver '{$driver}' not supported"),
        };
    }

    public static function work(int $maxAttempts = 3): void
    {
        $driver = self::driver();

        if ($driver === 'database') {
            self::workDatabase($maxAttempts);
        }
    }

    // ========== SYNC DRIVER (immediate execution) ==========

    private static function executeSyncJob(string $jobClass, array $data): void
    {
        if (!class_exists($jobClass)) {
            throw new \RuntimeException("Job class '{$jobClass}' not found");
        }

        $job = new $jobClass();
        if (method_exists($job, 'handle')) {
            $job->handle($data);
        }
    }

    // ========== DATABASE DRIVER ==========

    private static function pushDatabase(string $jobClass, array $data): void
    {
        if (!class_exists($jobClass)) {
            throw new \RuntimeException("Job class '{$jobClass}' not found");
        }

        $connection = \Footup\Orm\DB::connection();

        // Insert job into database
        $connection->insert('jobs', [
            'class' => $jobClass,
            'data' => json_encode($data),
            'attempts' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private static function workDatabase(int $maxAttempts = 3): void
    {
        $connection = \Footup\Orm\DB::connection();

        // Get all pending jobs
        $jobs = $connection->select('SELECT * FROM jobs WHERE failed_at IS NULL AND attempts < ? ORDER BY created_at ASC', [$maxAttempts]);

        foreach ($jobs as $jobRow) {
            try {
                $jobClass = $jobRow['class'];
                $jobData = json_decode($jobRow['data'], true);

                // Execute the job
                self::executeSyncJob($jobClass, $jobData);

                // Remove job from queue
                $connection->delete('jobs', ['id' => $jobRow['id']]);

            } catch (\Throwable $e) {
                // Increment attempts
                $connection->update('jobs', ['attempts' => $jobRow['attempts'] + 1], ['id' => $jobRow['id']]);

                // If max attempts reached, mark as failed
                if ($jobRow['attempts'] + 1 >= $maxAttempts) {
                    $connection->update('jobs', ['failed_at' => date('Y-m-d H:i:s')], ['id' => $jobRow['id']]);
                }
            }
        }
    }

    /**
     * Get number of pending jobs
     */
    public static function count(): int
    {
        $driver = self::driver();

        if ($driver !== 'database') {
            return 0;
        }

        $connection = \Footup\Orm\DB::connection();
        $result = $connection->selectOne('SELECT COUNT(*) as count FROM jobs WHERE failed_at IS NULL');

        return $result['count'] ?? 0;
    }

    /**
     * Clear all jobs from queue
     */
    public static function flush(): void
    {
        $driver = self::driver();

        if ($driver !== 'database') {
            return;
        }

        $connection = \Footup\Orm\DB::connection();
        $connection->delete('jobs', []);
    }
}
