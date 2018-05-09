<?php

namespace ESFoundation\CQRS;

interface CommandBus
{
    public function dispatch(Command $command);

    public function subscribe(CommandHandler $commandHandler, Command $command = null);
}