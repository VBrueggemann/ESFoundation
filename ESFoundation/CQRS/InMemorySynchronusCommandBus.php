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
        $commandHandler = $this->commandHandlers->get(get_class($command));

        if ($commandHandler) {
            $commandHandler->handle($command);
            return;
        }

        $this->commandHandlers->each(function ($commandHandler) use ($command) {
            return !$commandHandler->handle($command);
        });
    }

    public function subscribe(CommandHandler $commandHandler, string $command = null)
    {
        if ($command) {
            $this->commandHandlers->put($command, $commandHandler);
        }

        $this->commandHandlers->push($commandHandler);
    }
}