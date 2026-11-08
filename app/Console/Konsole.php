<?php

namespace App\Console;

use Footup\Cli\Konsole as CliKonsole;
use Override;

class Konsole extends CliKonsole {

  #[Override]
  public function discoverCommands(): void
  {
    parent::discoverCommands();
    
    $this->loadCommands(dirname(__DIR__) . '/Commands');

    $commands = require_once __DIR__ . '/../../routes/console.php';

    if (is_array($commands)) {
      foreach ($commands as $command) {
        if (is_string($command) && class_exists($command)) {
          /** @var \Footup\Cli\Input\Command $instance */
          $intance = new $command($this);
          $this->add($intance, $instance->alias());
        } elseif ($command instanceof \Footup\Cli\Input\Command) {
          $this->add($command, $command->alias()); 
        }
      }
    }
  }
}