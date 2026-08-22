<?php

declare(strict_types=1);

namespace Footup;

/*
|--------------------------------------------------------------------------
| Queue — simple file-based job queue
|
| Usage:
|   Queue::push(SendEmailJob::class, ['email' => 'user@example.com']);
|   // then: php bin/console.php queue:work
|--------------------------------------------------------------------------
*/

class Queue
{
    public static function push(string $jobClass, array $data = []): void
    {
        $dir = storage_path('queue');
        @mkdir($dir, 0775, true);

        $job = [
            'class' => $jobClass,
            'data' => $data,
            'attempts' => 0,
            'created_at' => time(),
        ];

        $file = $dir . '/' . uniqid('job_') . '.json';
        file_put_contents($file, json_encode($job));
    }

    public static function work(int $maxAttempts = 3): void
    {
        $dir = storage_path('queue');

        if (! is_dir($dir)) {
            return;
        }

        $files = glob($dir . '/job_*.json');

        foreach ($files as $file) {
            $job = json_decode(file_get_contents($file), true);

            if ($job['attempts'] >= $maxAttempts) {
                unlink($file);

                continue;
            }

            try {
                $class = $job['class'];
                (new $class())->handle($job['data']);
                unlink($file);
            } catch (\Throwable $e) {
                $job['attempts']++;
                file_put_contents($file, json_encode($job));
                Log::error("Job failed: {$e->getMessage()}", ['job' => $job['class']]);
            }
        }
    }
}
