<?php

namespace tests;

use ESFoundation\ES\DomainEventStream;
use ESFoundation\ES\EventSourcedAggregateRoot;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use Ramsey\Uuid\Uuid;

class EventSourcedTestAggregateRoot extends EventSourcedAggregateRoot
{
    public static function makeNewEventSourcedTestAggregateRoot(Collection $payload = null)
    {
        $aggregateRootId = new AggregateRootId(Uuid::uuid4()->toString());
        $values = new EventSourcedTestAggregateRootValues($aggregateRootId);
        self::applyThat(DomainEventStream::wrap(new TestEvent($aggregateRootId, $payload)), $values);
        return $values;
    }

    protected static function applyThatTestEvent(TestEvent $testEvent, EventSourcedTestAggregateRootValues $values)
    {
        if ($testEvent->first) {
            $values->put('first', $testEvent->first);
        }
        if ($testEvent->second) {
            $values->put('second', $testEvent->second);
        }
        return true;
    }
}