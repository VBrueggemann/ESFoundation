<?php

namespace tests;

use ESFoundation\ES\DomainEventStream;
use ESFoundation\ES\EventSourcedAggregateRoot;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use Ramsey\Uuid\Uuid;

class EventSourcedTestAggregateRoot extends EventSourcedAggregateRoot
{
    public static function makeNewEventSourcedTestAggregateRoot($payload = true)
    {
        $aggregateRootId = new AggregateRootId(Uuid::uuid4()->toString());
        $self = new self($aggregateRootId);
        $self->applyThat(DomainEventStream::wrap(new TestEvent($aggregateRootId, $payload)));
        return $self;
    }

    protected function applyThatTestEvent(TestEvent $testEvent)
    {
        return $testEvent->getPayload()->first;
    }
}