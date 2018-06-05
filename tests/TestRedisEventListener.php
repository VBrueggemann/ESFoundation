<?php

namespace tests;

use ESFoundation\ES\DomainEventStream;
use ESFoundation\ES\DomainStorageEvent;
use ESFoundation\ES\EventListener;
use ESFoundation\ES\DomainEvent;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\DomainEventId;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TestRedisEventListener extends EventListener
{
    public function handle(DomainEvent $event)
    {
        $redis = Redis::connection();
        $redis->unsubscribe();
        $redis->set('test1234', $event->serialize(true));
    }

    public function getHandledEvents(AggregateRootId $aggregateRootId)
    {
        $redis = Redis::connection();

        $storageEvent = DomainStorageEvent::fromJson($aggregateRootId, unserialize($redis->get('test1234')));
        return DomainEventStream::make()->push(DomainEvent::deserializePayload($storageEvent));
    }
}