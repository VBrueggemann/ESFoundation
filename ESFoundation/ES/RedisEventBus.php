<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\EventBus;
use ESFoundation\ES\Contracts\EventListener as EventListenerContract;
use ESFoundation\ES\Contracts\EventStore;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use Illuminate\Support\Facades\Redis;

class RedisEventBus implements EventBus
{
    private $eventStore;

    public function __construct(RedisEventStore $eventStore, EventListenerContract $listener = null)
    {
        $this->eventStore = $eventStore;
        if ($listener) {
            $this->subscribe($listener);
        }
    }

    public function dispatch(DomainEventStream $domainEventStream)
    {
        $domainEventStream->each(function ($domainEvent) {
            Redis::publish($domainEvent->getAggregateRootId()->value, $domainEvent->serialize());
        });

        $domainEventStream->each(function ($domainEvent) {
            Redis::publish('all', $domainEvent->serialize());
        });

        $this->eventStore->push($domainEventStream);
    }

    public function subscribe(EventListenerContract $eventListener, AggregateRootId $aggregateRootId = null)
    {
        Redis::subscribe([$aggregateRootId ? $aggregateRootId->value : 'all'],
            function ($domainEvent) use ($eventListener, $aggregateRootId) {
                $eventListener->handle(
                    DomainEvent::deserializePayload(
                        DomainStorageEvent::fromJson($aggregateRootId, unserialize($domainEvent))
                    )
                );
            }
        );
    }
}