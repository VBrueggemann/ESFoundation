<?php

use ESFoundation\ES\ValueObjects\AggregateRootId;

class AggregateRootIntegrationTest extends TestCase
{
    /**
     * @test
     */
    public function an_aggregate_root_is_initalizable()
    {
        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $event = new \tests\TestEvent($aggregateRootId, null);

        $aggregateRoot = \tests\EventSourcedTestAggregateRoot::initialize(\ESFoundation\ES\DomainEventStream::wrap($event));

        $this->assertEmpty($aggregateRoot->popUncommittedEvents());
        $this->assertEquals($aggregateRootId->value, $aggregateRoot->getAggregateRootId());
    }

    /**
     * @test
     */
    public function an_aggregate_root_is_constructable_via_named_constructor()
    {
        $aggregateRoot = \tests\EventSourcedTestAggregateRoot::makeNewEventSourcedTestAggregateRoot();

        $this->assertContainsOnly(\tests\TestEvent::class, $aggregateRoot->popUncommittedEvents());
    }

    /**
     * @test
     */
    public function an_event_can_be_applied_to_an_aggregate_root()
    {
        $aggregateRoot = \tests\EventSourcedTestAggregateRoot::makeNewEventSourcedTestAggregateRoot();

        $aggregateRoot->applyThat(
            \ESFoundation\ES\DomainEventStream::wrap(
                new \tests\TestEvent(new AggregateRootId($aggregateRoot->getAggregateRootId()), true)
            )
        );

        $events = $aggregateRoot->popUncommittedEvents();


        $this->assertContainsOnly(\tests\TestEvent::class, $events);
        $this->assertCount(2, $events);
    }


    /**
     * @test
     */
    public function an_aggregate_root_validates_before_applying()
    {
        $aggregateRoot = \tests\EventSourcedTestAggregateRoot::makeNewEventSourcedTestAggregateRoot();

        $aggregateRoot->applyThat(
            \ESFoundation\ES\DomainEventStream::wrap(
                new \tests\TestEvent(new AggregateRootId($aggregateRoot->getAggregateRootId()), 'test')
            )
        );

        $aggregateRoot->applyThat(
            \ESFoundation\ES\DomainEventStream::wrap(
                new \tests\TestEvent(new AggregateRootId($aggregateRoot->getAggregateRootId()), 'test')
            )
        );

        $events = $aggregateRoot->popUncommittedEvents();

        $this->assertContainsOnly(\tests\TestEvent::class, $events);
        $this->assertCount(1, $events);
    }
}
