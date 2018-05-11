<?php

use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\DomainEventId;

class EventStoreIntegrationTest extends TestCase
{
    /**
     * @test
     */
    public function an_event_can_be_stored()
    {
        $payload = collect([
            'first' => 'one',
            'second' => 'two'
        ]);

        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $event = new \tests\TestEvent(
            $aggregateRootId,
            $payload
        );

        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();

        $domainEventStream = \ESFoundation\ES\DomainEventStream::wrap($event);

        $errors = $eventStore->push($domainEventStream);

        $this->assertEmpty($errors);
        $this->assertContainsOnly($event, $eventStore->get($aggregateRootId));
    }

    /**
     * @test
     */
    public function multiple_events_can_be_stored()
    {
        $payload = collect([
            'first' => 'one',
            'second' => 'two'
        ]);

        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $event1 = new \tests\TestEvent(
            $aggregateRootId,
            $payload,
            0
        );

        $event2 = new \tests\TestEvent(
            $aggregateRootId,
            $payload,
            1
        );

        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();

        $domainEventStream = \ESFoundation\ES\DomainEventStream::wrap([$event1, $event2]);

        $errors = $eventStore->push($domainEventStream);

        $this->assertEmpty($errors);
        $this->assertContains($event1, $eventStore->get($aggregateRootId));
        $this->assertContains($event2, $eventStore->get($aggregateRootId));
    }

    /**
     * @test
     */
    public function events_must_have_different_playhead()
    {
        $payload = collect([
            'first' => 'one',
            'second' => 'two'
        ]);

        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $event1 = new \tests\TestEvent(
            $aggregateRootId,
            $payload,
            0
        );

        $event2 = new \tests\TestEvent(
            $aggregateRootId,
            $payload,
            0
        );

        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();

        $domainEventStream = \ESFoundation\ES\DomainEventStream::wrap([$event1, $event2]);

        $errors = $eventStore->push($domainEventStream);

        $this->assertContainsOnlyInstancesOf(\ESFoundation\ES\Errors\DuplicatePlayhead::class, $errors);
    }

    /**
     * @test
     */
    public function domain_event_stream_must_only_contain_domain_events()
    {
        $payload = collect([
            'first' => 'one',
            'second' => 'two'
        ]);

        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $event1 = new \tests\TestEvent(
            $aggregateRootId,
            $payload,
            0
        );

        $notEvent = collect();

        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();

        $domainEventStream = \ESFoundation\ES\DomainEventStream::wrap([$event1, $notEvent]);

        $errors = $eventStore->push($domainEventStream);

        $this->assertContainsOnlyInstancesOf(\ESFoundation\ES\Errors\NotADomainEvent::class, $errors);
    }
}
