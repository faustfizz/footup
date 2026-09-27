<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;

class QueueCommand extends Command
{
    public function __construct(App $cli)
    {
        parent::__construct("queue:work", "Start processing queue", false, $cli);

        $this->usage(
                // $0 will be interpolated to actual command name
                "<bold>  $0</end> <comment></end> ## that command don\"t take argument itself neither option !<eol/>"
            );

        $this->inGroup("Server")->alias("q");
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io): void
    {

    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute()
    {
        $io = $this->app()->io();
        
        // Get the party started.
        $io->info($this->app()->name() . "Processing queue...", true)->eol();
        \Footup\Queuing\Queue::work();
        $io->info($this->app()->name() . "Queue processed.", true)->eol();

        // If you return integer from here, that will be taken as exit error code
    }

}