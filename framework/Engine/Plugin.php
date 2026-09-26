<?php

namespace Footup\Engine;

abstract class Plugin
{
    abstract public function name(): string;
    public function boot(): void {}
    public function shutdown(): void {}
}
