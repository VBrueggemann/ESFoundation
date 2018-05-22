<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\AggregateRepository;
use ESFoundation\ES\Contracts\AggregateRoot;
use ESFoundation\ES\Contracts\EventStore;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use \ESFoundation\ES\Contracts\EventListener;

class InMemoryCashingAggregateRepository implements AggregateRepository, EventListener
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
            return new NoAggregateRoot();
        }

        if (!($playhead <= 0)) {
            return $aggregateRootClass::initialize($domainEventStream->take($playhead));
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

    public function handle(DomainEvent $domainEvent)
    {
        $aggregateRootId = $domainEvent->getAggregateRootId()->value;

        if (key_exists($aggregateRootId, $this->cachedAggregates)) {
            $cached = $this->cachedAggregates[$aggregateRootId];
            $cached->applyThat($domainEvent);
            $cached->popUncommittedEvents();
        }
    }
}