<?php

namespace tests;

use ESFoundation\ES\EventListener;
use ESFoundation\ES\DomainEvent;

class TestEventListener extends EventListener
{
    private $handledEvents = [];

    public function handle(DomainEvent $event)
    {
        array_push($this->handledEvents, get_class($event));
    }

    /**
     * @return array
     */
    public function getHandledEvents(): array
    {
        return $this->handledEvents;
    }
}