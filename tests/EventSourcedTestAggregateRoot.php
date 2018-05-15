<?php

namespace tests;

use ESFoundation\ES\EventSourcedAggregateRoot;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use Ramsey\Uuid\Uuid;

class EventSourcedTestAggregateRoot extends EventSourcedAggregateRoot
{
    public static function makeNewEventSourcedTestAggregateRoot($payload = true)
    {
        $aggregateRootId = new AggregateRootId(Uuid::uuid4()->toString());
        return new self($aggregateRootId, new TestEvent($aggregateRootId, $payload));
    }

    protected function applyThatTestEvent(TestEvent $testEvent)
    {
        return $testEvent->getPayload()->first;
    }
}