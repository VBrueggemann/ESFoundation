<?php

namespace ESFoundation\CQRS\Contracts;

use ESFoundation\CQRS\Command;
use ESFoundation\CQRS\CommandHandler;

interface CommandBus
{
    public function dispatch(Command $command);

    public function subscribe(CommandHandler $commandHandler, string $command = null);
}