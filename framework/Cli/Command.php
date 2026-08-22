<?php

declare(strict_types=1);

namespace Footup\Cli;

/*
|--------------------------------------------------------------------------
| Command — base class for CLI commands
|
| Usage:
|   class MigrateCommand extends Command {
|       public function handle(): void {
|           $this->info('Running migrations...');
|       }
|   }
|--------------------------------------------------------------------------
*/

abstract class Command
{
    public function __construct(
        protected array $arguments = [],
        protected array $options = []
    ) {
    }

    abstract public function handle(): void;

    protected function info(string $message): void
    {
        fwrite(STDOUT, $message . "\n");
    }

    protected function error(string $message): void
    {
        fwrite(STDERR, $message . "\n");
    }

    protected function argument(string $name): ?string
    {
        return $this->arguments[$name] ?? null;
    }

    protected function option(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }
}
