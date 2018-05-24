<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\AggregateProjectionRepository;
use ESFoundation\ES\Contracts\AggregateRoot;
use ESFoundation\ES\Contracts\EventStore;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\AggregateRootProjection;

class NonCachingAggregateProjectionRepository implements AggregateProjectionRepository
{
    private $eventStore;

    function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function load(AggregateRootId $aggregateRootId, string $aggregateRootClass, int $playhead = 0) : ?AggregateRootProjection
    {
        $domainEventStream = $this->eventStore->get($aggregateRootId);

        if ($domainEventStream->isEmpty()) {
            return null;
        }

        if (!(class_exists($aggregateRootClass) && isset(class_implements($aggregateRootClass)[AggregateRoot::class]))) {
            return null;
        }

        return $aggregateRootClass::initialize($playhead <= 0 ? $domainEventStream : $domainEventStream->take($playhead));
    }
}