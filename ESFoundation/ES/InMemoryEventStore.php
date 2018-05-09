<?php

namespace ESFoundation\ES;

use ESFoundation\ES\ValueObjects\AggregateRootId;

class InMemoryNonAtomicEventStore implements EventStore
{
    private $events = [];

    public function push(DomainEventStream $domainEventStream, $meta = null)
    {
        $errors = collect();
        foreach ($domainEventStream as $index => $domainEvent) {
            if ($domainEventStream->guard($index)) {
                $errors->put($index, new NotADomainMessage());
                break;
            }

            $aggregateRootId = $domainEvent->getAggregateRootId()->value;
            if (!isset($this->events[$aggregateRootId])) {
                $this->events[$aggregateRootId] = [];
            }

            $playhead = $domainEvent->getPlayhead();
            if (isset($this->events[$aggregateRootId][$playhead])) {
                $errors->put($index, new DuplicatePlayhead());
                break;
            }
            $this->events[$aggregateRootId][$playhead] = $domainEvent;
        }
        return $errors;
    }

    public function get(AggregateRootId $aggregateRootId, int $playhead)
    {
        if (!isset($this->events[$aggregateRootId->value])) {
            return new DomainEventStream([]);
        }

        return new DomainEventStream(
            array_values(
                array_filter(
                    $this->events[$aggregateRootId->value],
                    function ($domainEvent) use ($playhead) {
                        return $playhead <= $domainEvent->getPlayhead();
                    }
                )
            )
        );
    }
}