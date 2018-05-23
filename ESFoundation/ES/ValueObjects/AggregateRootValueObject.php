<?php

namespace ESFoundation\ES\ValueObjects;

use ESFoundation\ES\DomainEvent;
use ESFoundation\ES\DomainEventStream;
use ESFoundation\ES\Errors\DuplicatePlayhead;
use ESFoundation\ValueObjects\GroupedValueObject;
use Illuminate\Support\Collection;

abstract class AggregateRootValueObject extends GroupedValueObject
{
    private $aggregateRootId;
    private $uncommittedEvents;
    private $playhead = -1;

    public function __construct(AggregateRootId $aggregateRootId, Collection $values = null)
    {
        $this->aggregateRootId = $aggregateRootId->value;
        $this->uncommittedEvents = DomainEventStream::make();
        parent::__construct($values);
    }

    public function popUncommittedEvents(): DomainEventStream
    {
        $uncommittedEvents = $this->uncommittedEvents;

        $this->uncommittedEvents = collect();

        return $uncommittedEvents;
    }

    /**
     * @return int
     */
    public function getPlayhead(): int
    {
        return $this->playhead;
    }

    /**
     * @param int $playhead
     */
    public function setPlayhead(int $playhead)
    {
        throw_if($playhead <= $this->playhead, DuplicatePlayhead::class);
        $this->playhead = $playhead;
    }

    public function getAggregateRootId()
    {
        return $this->aggregateRootId;
    }

    public function pushToUncommittedEvents(DomainEvent $domainEvent)
    {
        $this->uncommittedEvents->push($domainEvent);
    }
}