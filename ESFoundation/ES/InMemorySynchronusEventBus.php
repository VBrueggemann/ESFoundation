<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\EventBus;
use ESFoundation\ES\Contracts\EventListener as EventListenerContract;
use ESFoundation\ES\Contracts\EventStore;
use ESFoundation\ES\ValueObjects\AggregateRootId;

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
        $aggregateRootIdListeners = $this->specificEventListeners->get($domainEventStream->first()->getAggregateRootId()->value);
        if ($aggregateRootIdListeners) {
            $domainEventStream->each(function ($domainEvent) use ($aggregateRootIdListeners) {
                $aggregateRootIdListeners->each(function ($listener) use ($domainEvent){
                    $listener->handle($domainEvent);
                });
            });
        }

        $this->globalEventListeners->each(function ($eventListener) use ($domainEventStream) {
            $domainEventStream->each(function ($domainEvent) use ($eventListener) {
                $eventListener->handle($domainEvent);
            });
        });

        if ($this->eventStore) {
            $this->eventStore->push($domainEventStream);
        }
    }

    public function subscribe(EventListenerContract $eventListener, AggregateRootId $aggregateRootId = null)
    {
        if ($aggregateRootId) {
            $aggregateRootIdListeners = $this->specificEventListeners->get($aggregateRootId->value);
            if ($aggregateRootIdListeners) {
                $aggregateRootIdListeners->push($eventListener);
                return;
            }
            $this->specificEventListeners->put($aggregateRootId->value, collect([$eventListener]));
            return;
        }
        $this->globalEventListeners->push($eventListener);
    }
}