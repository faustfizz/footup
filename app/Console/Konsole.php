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

    require_once __DIR__ . '/../../routes/console.php';
  }
}