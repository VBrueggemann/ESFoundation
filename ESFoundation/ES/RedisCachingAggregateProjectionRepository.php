<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\AggregateProjectionRepository;
use ESFoundation\ES\Contracts\AggregateRoot;
use ESFoundation\ES\Contracts\EventListener;
use ESFoundation\ES\Contracts\EventStore;
use ESFoundation\ES\Errors\NoAggregateRoot;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\AggregateRootProjection;
use Illuminate\Support\Facades\Redis;

class RedisCachingAggregateProjectionRepository implements AggregateProjectionRepository
{
    private $eventStore;

    function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function load(AggregateRootId $aggregateRootId, string $aggregateRootClass, int $playhead = 0) : ?AggregateRootProjection
    {
        throw_if(!(class_exists($aggregateRootClass) && isset(class_implements($aggregateRootClass)[AggregateRoot::class])),
            NoAggregateRoot::class
        );

        if (!($playhead <= 0)) {
            $domainEventStream = $this->eventStore->get($aggregateRootId);
            return $aggregateRootClass::initialize($domainEventStream->take($playhead));
        }

        $redis = Redis::connection('aggregates');

        if (($cachedSerialized = $redis->get($aggregateRootId->value))) {
            $cachedAggregate = AggregateRootProjection::deserialize($cachedSerialized);
            $unappliedEvents = $this->eventStore->get($aggregateRootId, $cachedAggregate->getPlayhead + 1);
            if ($unappliedEvents->isNotEmpty()) {
                $aggregateRootClass::represent($unappliedEvents, $cachedAggregate, false);
                $redis->set($aggregateRootId->value, $cachedAggregate->serialize());
            }
            return $cachedAggregate;
        }

        $domainEventStream = $this->eventStore->get($aggregateRootId);

        if ($domainEventStream->isEmpty()) {
            return null;
        }

        $aggregateProjection = $aggregateRootClass::initialize($domainEventStream);
        $redis->set($aggregateRootId->value, $aggregateProjection->serialize());
        return $aggregateProjection;
    }
}