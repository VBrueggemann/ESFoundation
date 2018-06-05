<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\EventStore;
use ESFoundation\ES\Errors\NoAggregateRootId;
use ESFoundation\ES\Errors\NotADomainEvent;
use ESFoundation\ES\Errors\WrongPlayhead;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;

class RedisEventStore implements EventStore
{
    public function push(DomainEventStream $domainEventStream, $meta = null)
    {
        $redis = Redis::connection('events');
        $nextPlayhead = $redis->llen($domainEventStream->first()->getAggregateRootId());
        $redis->multi();
        foreach ($domainEventStream as $index => $domainEvent) {
            if ($domainEventStream->guard($index)) {
                $redis->discard();
                throw new NotADomainEvent();
            }

            $aggregateRootId = $domainEvent->getAggregateRootId()->value;
            if (!$aggregateRootId) {
                $redis->discard();
                throw new NoAggregateRootId();
            }

            $playhead = $domainEvent->getPlayhead();

            if ($nextPlayhead != $playhead) {
                $redis->discard();
                throw new WrongPlayhead();
            }

            if (!$domainEvent->getCreatedAt()) {
                $domainEvent->setCreatedAt(Carbon::now());
            }

            $redis->rpush($aggregateRootId, $domainEvent->serialize());
            $nextPlayhead = $nextPlayhead + 1;
        }
        return $redis->exec();
    }

    public function get(AggregateRootId $aggregateRootId, int $playhead = 0): DomainEventStream
    {
        $redis = Redis::connection('events');

        $redisList = $redis->lrange($aggregateRootId->value, $playhead, -1);

        $stream = DomainEventStream::make();

        foreach ($redisList as $redisElement) {
            $storageEvent = DomainStorageEvent::fromJson($aggregateRootId, unserialize($redisElement));
            $stream->push(DomainEvent::deserializePayload($storageEvent));
        }

        return $stream;
    }
}
