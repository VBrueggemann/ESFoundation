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
        $aggregateRootValues = \tests\EventSourcedTestAggregateRoot::makeNewEventSourcedTestAggregateRoot();

        \tests\EventSourcedTestAggregateRoot::applyThat(
            \ESFoundation\ES\DomainEventStream::wrap(
                new \tests\TestEvent(new AggregateRootId($aggregateRootValues->getAggregateRootId()), ['first' => 'one'])
            ),
            $aggregateRootValues
        );

        $events = $aggregateRootValues->popUncommittedEvents();


        $this->assertContainsOnly(\tests\TestEvent::class, $events);
        $this->assertCount(2, $events);
    }

    /**
     * @test
     */
    public function an_aggregate_root_validates_before_applying()
    {
        $aggregateRootValues = \tests\EventSourcedTestAggregateRoot::makeNewEventSourcedTestAggregateRoot();

        try {
            \tests\EventSourcedTestAggregateRoot::applyThat(
                \ESFoundation\ES\DomainEventStream::wrap(
                    new \tests\TestEvent(new AggregateRootId($aggregateRootValues->getAggregateRootId()), ['first' => 'second'])
                ),
                $aggregateRootValues
            );
        } catch (\ESFoundation\ES\Errors\FailedValidation $exception) {
            $events = $aggregateRootValues->popUncommittedEvents();

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
        $aggregateRootValues = \tests\EventSourcedTestAggregateRoot::makeNewEventSourcedTestAggregateRoot(collect(['first' => 'one','second' => 'two']));
        $aggregateRootValues->popUncommittedEvents();

        $serialized = $aggregateRootValues->serialize();
        $deserialized = AggregateRootProjection::deserialize($serialized);

        $this->assertEquals($deserialized->first, $aggregateRootValues->first);
        $this->assertEquals($deserialized->second, $aggregateRootValues->second);
    }
}
