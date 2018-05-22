<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\EventBus;
use ESFoundation\ES\Contracts\EventListener as EventListenerContract;

class InMemorySynchronusEventBus implements EventBus
{
    private $globalEventListeners;
    private $specificEventListeners;

    public function __construct()
    {
        $this->globalEventListeners = collect();
        $this->specificEventListeners = collect();
    }

    public function dispatch(DomainEventStream $domainEventStream)
    {
        $domainEventStream->each(function ($domainEvent) {
            $eventListener = $this->specificEventListeners->get(get_class($domainEvent));

            if ($eventListener) {
                $eventListener->handle($domainEvent);
            }
        });

        $this->globalEventListeners->each(function ($eventListener) use ($domainEventStream) {
            $domainEventStream->each(function ($domainEvent) use ($eventListener) {
                $eventListener->handle($domainEvent);
            });
        });
    }

    public function subscribe(EventListenerContract $eventListener, string $domainEvent = null)
    {
        if ($domainEvent) {
            $this->specificEventListeners->put($domainEvent, $eventListener);
            return;
        }
        $this->globalEventListeners->push($eventListener);
    }
}