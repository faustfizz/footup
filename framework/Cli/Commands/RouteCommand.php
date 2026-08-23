<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;

class RouteCommand extends Command
{
    public function __construct(App $cli)
    {
        parent::__construct("route:list", "Registered routes list", false, $cli);

        $this->usage(
                // $0 will be interpolated to actual command name
                "<bold>  $0</end> <comment></end> ## List all available routes<eol/>"
            );

        $this->inGroup("Routes")->alias("rl");
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

        foreach (app()->router()->all() as $route) {
            $io->green($route->context . ' ')->info(str_pad($route->method, 8) . $route->uri, true)->eol();
        }

        // If you return integer from here, that will be taken as exit error code
    }

}