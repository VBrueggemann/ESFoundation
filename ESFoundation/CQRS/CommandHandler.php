<?php

namespace ESFoundation\CQRS;

abstract class CommandHandler
{
    protected $handleMethods = [];

    public function handle(Command $command)
    {
        $method = $this->getHandleMethod($command);

        if (!method_exists($this, $method)) {
            return;
        }

        $this->$method($command);
    }

    private function getHandleMethod(Command $command)
    {
        if (($method = $this->handleMethods[get_class($command)])) {
            return $method;
        }

        $classParts = explode('\\', get_class($command));

        return 'handle'.end($classParts);
    }
}