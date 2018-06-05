<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\AggregateProjectionRepository;
use ESFoundation\ES\Contracts\AggregateRoot;
use ESFoundation\ES\Contracts\EventListener;
use ESFoundation\ES\Contracts\EventStore;
use ESFoundation\ES\Errors\NoAggregateRoot;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\AggregateRootProjection;

class InMemoryCachingAggregateProjectionRepository implements AggregateProjectionRepository, EventListener
{
    private $eventStore;
    private $cachedAggregateValues = [];

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

        if (key_exists($aggregateRootId->value, $this->cachedAggregateValues)) {
            $cached = $this->cachedAggregateValues[$aggregateRootId->value];
            $unappliedEvents = $this->eventStore->get($aggregateRootId, $cached->getPlayhead() + 1);
            if ($unappliedEvents->isNotEmpty()) {
                $aggregateRootClass::represent($unappliedEvents, $cached, false);
            }
            return $cached->clone();
        }

        $domainEventStream = $this->eventStore->get($aggregateRootId);

        if ($domainEventStream->isEmpty()) {
            return null;
        }

        $aggregateValues = $aggregateRootClass::initialize($domainEventStream);
        $this->cachedAggregateValues[$aggregateRootId->value] = $aggregateValues;
        return $aggregateValues->clone();
    }

    public function handle(DomainEvent $domainEvent)
    {
        $aggregateRootId = $domainEvent->getAggregateRootId()->value;

        if (key_exists($aggregateRootId, $this->cachedAggregateValues)) {
            $cached = $this->cachedAggregateValues[$aggregateRootId];
            $aggregateRoot = $cached->getAggregateRoot();
            $aggregateRoot::represent(DomainEventStream::wrap($domainEvent), $cached, false);
        }
    }
}