<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\EventStore;
use ESFoundation\ES\Errors\DuplicatePlayhead;
use ESFoundation\ES\Errors\NotADomainEvent;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use Illuminate\Support\Carbon;
use \ESFoundation\ES\Contracts\EventListener;

class InMemoryNonAtomicEventStore implements EventStore
{
    private $events = [];

    public function push(DomainEventStream $domainEventStream, $meta = null)
    {
        $errors = collect();
        foreach ($domainEventStream as $index => $domainEvent) {
            if ($domainEventStream->guard($index)) {
                $errors->put($index, new NotADomainEvent());
                break;
            }

            $aggregateRootId = $domainEvent->getAggregateRootId()->value;
            if (!$aggregateRootId) {
                $errors->put($index, new NoAggregateRootId());
                break;
            }

            if (!isset($this->events[$aggregateRootId])) {
                $this->events[$aggregateRootId] = [];
            }

            $playhead = $domainEvent->getPlayhead();
            if (isset($this->events[$aggregateRootId][$playhead])) {
                $errors->put($index, new DuplicatePlayhead());
                break;
            }

            if (!$domainEvent->getCreatedAt()) {
                $domainEvent->setCreatedAt(Carbon::now());
            }
            $this->events[$aggregateRootId][$playhead] = $domainEvent;
        }
        return $errors;
    }

    public function get(AggregateRootId $aggregateRootId, int $playhead = 0): DomainEventStream
    {
        if (!isset($this->events[$aggregateRootId->value])) {
            return DomainEventStream::make([]);
        }

        return DomainEventStream::make(
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
