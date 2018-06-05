<?php

namespace ESFoundation\ES\ValueObjects;

use ESFoundation\ES\DomainEvent;
use ESFoundation\ES\DomainEventStream;
use ESFoundation\ES\Errors\DuplicatePlayhead;
use ESFoundation\ValueObjects\GroupedValueObject;
use Illuminate\Support\Collection;
use Serializable;

abstract class AggregateRootProjection extends GroupedValueObject
{
    private $aggregateRootId;
    private $uncommittedEvents;
    private $playhead = -1;
    private $aggregateRoot;

    public function __construct(AggregateRootId $aggregateRootId, Collection $values = null, string $aggregateRoot = '')
    {
        $this->aggregateRootId = $aggregateRootId->value;
        $this->uncommittedEvents = DomainEventStream::make();
        $this->aggregateRoot = $aggregateRoot ?: substr(get_class($this), 0, -6);
        parent::__construct($values);
    }

    public function serialize()
    {
        $values = $this->values->map(function ($item, $key){
            if ($item instanceof GroupedValueObject) {
                return $item->serialize();
            }
            return $item->value;
        });
        return serialize(collect([
            'values' => $values,
            'aggregateRootId' => $this->aggregateRootId,
            'playhead' => $this->playhead,
            'aggregateRoot' => $this->aggregateRoot,
            'class' => get_class($this),
        ])->toJson());
    }

    public static function deserialize($serialized)
    {
        $json = json_decode(unserialize($serialized), true);
        $class = $json['class'];
        return new $class(new AggregateRootId($json['aggregateRootId']), collect($json['values']));
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

    /**
     * @return bool|string
     */
    public function getAggregateRoot()
    {
        return $this->aggregateRoot;
    }

    public function pushToUncommittedEvents(DomainEvent $domainEvent)
    {
        $this->uncommittedEvents->push($domainEvent);
    }

    public function applyThat(DomainEventStream $domainEventStream)
    {
        $this->aggregateRoot::applyThat($domainEventStream, $this);
        return $this;
    }

    public function clone()
    {
        $clone = parent::clone();
        $clone->aggregateRootId = $this->aggregateRootId;
        $clone->playhead = $this->playhead;
        $clone->aggregateRoot = $this->aggregateRoot;
        return $clone;
    }
}