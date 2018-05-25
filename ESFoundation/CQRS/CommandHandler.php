<?php

namespace ESFoundation\CQRS;

abstract class CommandHandler
{
    protected $handleMethods = [];

    public function handle(Command $command): bool
    {
        $method = $this->getHandleMethod($command);

        if (!method_exists($this, $method)) {
            return false;
        }

        return $this->$method($command);
    }

    private function getHandleMethod(Command $command)
    {
        if (array_key_exists(get_class($command), $this->handleMethods)) {
            return $this->handleMethods[get_class($command)];
        }

        $classParts = explode('\\', get_class($command));

        return 'handle'.end($classParts);
    }
}