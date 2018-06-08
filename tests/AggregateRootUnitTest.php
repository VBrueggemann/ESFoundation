<?php

use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\AggregateRootProjection;

class AggregateRootUnitTest extends TestCase
{
    /**
     * @test
     */
    public function an_aggregate_root_is_initalizable()
    {
        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $event = new \tests\TestEvent($aggregateRootId, ['first' => 'one']);

        $AggregateRootProjection = \tests\EventSourcedTestAggregateRoot::initialize(\ESFoundation\ES\DomainEventStream::wrap($event));

        $this->assertEmpty($AggregateRootProjection->popUncommittedEvents());
        $this->assertEquals($aggregateRootId->value, $AggregateRootProjection->getAggregateRootId());
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
        $aggregateRootProjection = \tests\EventSourcedTestAggregateRoot::makeNewEventSourcedTestAggregateRoot();

        \tests\EventSourcedTestAggregateRoot::applyThat(
            \ESFoundation\ES\DomainEventStream::wrap(
                new \tests\TestEvent(new AggregateRootId($aggregateRootProjection->getAggregateRootId()), ['first' => 'one'])
            ),
            $aggregateRootProjection
        );

        $events = $aggregateRootProjection->popUncommittedEvents();


        $this->assertContainsOnly(\tests\TestEvent::class, $events);
        $this->assertCount(2, $events);
    }

    /**
     * @test
     */
    public function an_aggregate_root_validates_before_applying()
    {
        $aggregateRootProjection = \tests\EventSourcedTestAggregateRoot::makeNewEventSourcedTestAggregateRoot();

        try {
            \tests\EventSourcedTestAggregateRoot::applyThat(
                \ESFoundation\ES\DomainEventStream::wrap(
                    new \tests\TestEvent(new AggregateRootId($aggregateRootProjection->getAggregateRootId()), ['first' => 'second'])
                ),
                $aggregateRootProjection
            );
        } catch (\ESFoundation\ES\Errors\FailedValidation $exception) {
            $events = $aggregateRootProjection->popUncommittedEvents();

            $this->assertContainsOnly(\tests\TestEvent::class, $events);
            $this->assertCount(1, $events);
            return;
        }

        $this->assertTrue(false);
    }

    /**
     * @test
     */
    public function an_aggregate_root_projection_is_serializable()
    {
        $aggregateRootProjection = \tests\EventSourcedTestAggregateRoot::makeNewEventSourcedTestAggregateRoot(collect(['first' => 'one','second' => 'two']));
        $aggregateRootProjection->popUncommittedEvents();

        $serialized = $aggregateRootProjection->serialize();
        $deserialized = AggregateRootProjection::deserialize($serialized);

        $this->assertEquals($deserialized->first, $aggregateRootProjection->first);
        $this->assertEquals($deserialized->second, $aggregateRootProjection->second);
    }
}
