<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\EventBus;
use ESFoundation\ES\Contracts\EventListener as EventListenerContract;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use Illuminate\Support\Facades\Redis;
use tests\TestRedisListener;
use tests\TestRedisListenerJob;

class RedisEventBus implements EventBus
{
    private $eventStore;

    public function __construct(RedisEventStore $eventStore = null, EventListenerContract $listener = null)
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
            Redis::publish('all', $domainEvent->serialize(true));
        });

        if ($this->eventStore) {
            $this->eventStore->push($domainEventStream);
        }
    }

    public function subscribe(EventListenerContract $eventListener, AggregateRootId $aggregateRootId = null)
    {
        dispatch(new TestRedisListenerJob($eventListener, $aggregateRootId));
    }
}