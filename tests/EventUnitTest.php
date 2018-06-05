<?php

use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\DomainEventId;

class EventUnitTest extends TestCase
{
    /**
     * @test
     */
    public function an_event_can_have_a_payload()
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

        $this->assertEquals($event->getPayload(), $payload);
        $this->assertEquals($event->getAggregateRootId()->value, $aggregateRootId);
        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($event->getId()));
    }

    /**
     * @test
     */
    public function an_events_payload_is_serializable()
    {
        $payload = [
            'first' => 'one',
            'second' => 'two'
        ];

        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $event = new \tests\TestEvent(
            $aggregateRootId,
            $payload
        );

        $this->assertEquals($payload, $event->serializePayload());
    }

    /**
     * @test
     */
    public function an_events_payload_is_unserializable()
    {
        $aggregateRootId = new AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $id = new DomainEventId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $createdAt = \Illuminate\Support\Carbon::now();

        $payload = [
            'first' => 'one',
            'second' => 'two'
        ];

        $domainStorageEvent = new \ESFoundation\ES\DomainStorageEvent(
            $aggregateRootId,
            $id,
            $createdAt,
            0,
            $payload,
            \tests\TestEvent::class
        );

        $domainEvent = \ESFoundation\ES\DomainEvent::deserializePayload($domainStorageEvent);

        $this->assertEquals($domainEvent->first, 'one');
        $this->assertEquals($domainEvent->second, 'two');

        $this->assertEquals($domainEvent->getId(), $id);
        $this->assertEquals($domainEvent->getAggregateRootId(), $aggregateRootId);
        $this->assertEquals($domainEvent->getCreatedAt(), $createdAt);
        $this->assertEquals($domainEvent->getPlayhead(), 0);
    }
}
