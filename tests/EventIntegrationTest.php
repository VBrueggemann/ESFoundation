<?php

class EventIntegrationTest extends TestCase
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
        $payload = collect([
            'first' => 'one',
            'second' => 'two'
        ]);

        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $event = new \tests\TestEvent(
            $aggregateRootId,
            $payload
        );

        $this->assertEquals(serialize($payload->toJson()), $event->serializePayload());
    }

    /**
     * @test
     */
    public function an_events_payload_is_unserializable()
    {
        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $id = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $createdAt = \Illuminate\Support\Carbon::now();

        $payload = serialize(collect([
            'first' => 'one',
            'second' => 'two'
        ])->toJson());

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
