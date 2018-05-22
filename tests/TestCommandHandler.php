<?php

namespace tests;

use ESFoundation\CQRS\Command;
use ESFoundation\CQRS\CommandHandler;

class TestCommandHandler extends CommandHandler
{
    private $handledCommands = [];

    public function handle(Command $command): bool
    {
        array_push($this->handledCommands, get_class($command));
        return true;
    }

    /**
     * @return array
     */
    public function getHandledCommands(): array
    {
        return $this->handledCommands;
    }
}