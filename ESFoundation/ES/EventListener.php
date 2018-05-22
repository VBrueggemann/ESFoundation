<?php

namespace ESFoundation\CQRS;

use ESFoundation\ES\DomainEvent;
use \ESFoundation\ES\EventListener as EventListenerContract;

abstract class EventListener implements EventListenerContract
{
    protected $handleMethods = [];

    public function handle(DomainEvent $domainEvent)
    {
        $method = $this->getHandleMethod($domainEvent);

        if (!method_exists($this, $method)) {
            return;
        }

        $this->$method($domainEvent);
    }

    private function getHandleMethod(DomainEvent $domainEvent)
    {
        if (array_key_exists(get_class($domainEvent), $this->handleMethods)) {
            return $this->handleMethods[get_class($domainEvent)];
        }

        $classParts = explode('\\', get_class($domainEvent));

        return 'handle'.end($classParts);
    }
}