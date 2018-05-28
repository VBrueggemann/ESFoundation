<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\EventBus;
use ESFoundation\ES\Contracts\EventListener as EventListenerContract;
use ESFoundation\ES\Contracts\EventStore;

class InMemorySynchronusEventBus implements EventBus
{
    private $globalEventListeners;
    private $specificEventListeners;
    private $eventStore;

    public function __construct(EventStore $eventStore = null, EventListenerContract $listener = null)
    {
        $this->globalEventListeners = collect();
        $this->specificEventListeners = collect();
        $this->eventStore = $eventStore;
        if ($listener) {
            $this->subscribe($listener);
        }
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

        if ($this->eventStore) {
            $this->eventStore->push($domainEventStream);
        }
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