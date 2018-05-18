<?php

namespace ESFoundation\ES;

use ESFoundation\ES\ValueObjects\AggregateRootId;

class InMemoryCashingAggregateRepository implements AggregateRepository
{
    private $eventStore;
    private $cachedAggregates = [];

    function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function load(AggregateRootId $aggregateRootId, string $aggregateRootClass, int $playhead = 0) : ?AggregateRoot
    {
        $domainEventStream = $this->eventStore->get($aggregateRootId);

        if ($domainEventStream->isEmpty()) {
            return null;
        }

        if (!(class_exists($aggregateRootClass) && isset(class_implements($aggregateRootClass)[AggregateRoot::class]))) {
            return null;
        }

        if (!$playhead) {
            return $aggregateRootClass::initialize($playhead <= 0 ? $domainEventStream : $domainEventStream->take($playhead));
        }

        if (key_exists($aggregateRootId->value, $this->cachedAggregates)) {
            $cached = $this->cachedAggregates[$aggregateRootId->value];
            $unappliedEvents = $this->eventStore->get($aggregateRootId, $cached->getPlayhead());
            $cached->applyThat($unappliedEvents);
            $cached->popUncommittedEvents();
            return $cached;
        }

        $aggregate = $aggregateRootClass::initialize($domainEventStream);
        $this->cachedAggregates[$aggregateRootId->value] = $aggregate;
        return $aggregate;
    }
}