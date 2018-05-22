<?php

namespace ESFoundation\CQRS;

use ESFoundation\CQRS\Contracts\CommandBus;

class InMemorySynchronusCommandBus implements CommandBus
{
    private $commandHandlers;

    public function __construct()
    {
        $this->commandHandlers = collect();
    }

    public function dispatch(Command $command)
    {
        $this->commandHandlers->each(function ($commandHandler) use ($command) {
            $commandHandler->handle($command);
        });
    }

    public function subscribe(CommandHandler $commandHandler, string $command = null)
    {
        $this->commandHandlers->push($commandHandler);
    }
}