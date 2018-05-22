<?php

namespace ESFoundation\CQRS;

use ESFoundation\ES\DomainEventStream;
use ESFoundation\ES\EventBus;
use ESFoundation\ES\Contracts\EventListener as EventListenerContract;

class InMemorySynchronusEventBus implements EventBus
{
    private $eventListeners;

    public function __construct()
    {
        $this->eventListeners = collect();
    }

    public function dispatch(DomainEventStream $domainEvent)
    {
        $this->eventListeners->each(function ($eventListener) use ($domainEvent) {
            $eventListener->handle($domainEvent);
        });
    }

    public function subscribe(EventListener $eventListener, DomainEvent $event = null)
    {
        $this->eventListeners->push($eventListener);
    }
}