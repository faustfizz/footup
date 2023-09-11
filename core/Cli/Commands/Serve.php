<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;

class Serve extends Command
{
    protected int $portOffset = 0;

    protected int $retries = 5;
    public function __construct(App $cli)
    {
        $this
            ->option("-o --host", "The HTTP host name or ip [default: localhost]", null, "localhost")
            ->option("-p --port", "The HTTP host port [default: 8080]", null, 8080)
            // Usage examples:
            ->usage(
                // $0 will be interpolated to actual command name
                "<bold>  $0</end> <comment> --host footup.dev </end> ## that command don\"t take argument itself but only option !<eol/>"
            );

        $this->inGroup("Server")->alias("s");

        parent::__construct("serve", "Start the " . $cli->name() . " development server", false, $cli);
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
        /**
         * @var string
         */
        $host = $this->host;
        /**
         * @var int
         */
        $port = ((int) $this->port + $this->portOffset);

        // Get the party started.
        $io->info($this->app()->name() . " development server start for the attempt : " . ($this->portOffset + 1) . " on http://$host:$port", true)
            ->green("Press Control-C to stop !", true)->eol();

        // Set the BASE_PATH as the working directory (public)
        chdir(BASE_PATH);
        // Set the Front Controller path as Document Root.
        $docroot = escapeshellarg(BASE_PATH);
        // Server file
        $server = escapeshellarg(ROOT_PATH . "server.php");

        // Call PHP's built-in webserver, making sure to set our environment is set and it simulates basic mod_rewrite.
        passthru(PHP_BINARY . " -S $host:$port -t $docroot $server", $status);

        // If the code reach here is that the server didn't start
        if ($status && $this->portOffset < $this->retries) {
            $this->portOffset++;
            $io->warn("The port $port may be in use, we restart with port " . ($this->port + 1), true)->eol();
            // Yo retry the serve command
            $this->execute();
        }

        // If you return integer from here, that will be taken as exit error code
    }

}