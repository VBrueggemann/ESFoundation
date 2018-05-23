<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\AggregateRepository;
use ESFoundation\ES\Contracts\AggregateRoot;
use ESFoundation\ES\Contracts\EventStore;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\AggregateRootValueObject;

class NonCachingAggregateRepository implements AggregateRepository
{
    private $eventStore;

    function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function load(AggregateRootId $aggregateRootId, string $aggregateRootClass, int $playhead = 0) : ?AggregateRootValueObject
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