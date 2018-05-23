<?php

use ESFoundation\ES\ValueObjects\AggregateRootId;

class AggregateRootUnitTest extends TestCase
{
    /**
     * @test
     */
    public function an_aggregate_root_is_initalizable()
    {
        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $event = new \tests\TestEvent($aggregateRootId, ['first' => 'one']);

        $aggregateRootValueObject = \tests\EventSourcedTestAggregateRoot::initialize(\ESFoundation\ES\DomainEventStream::wrap($event));

        $this->assertEmpty($aggregateRootValueObject->popUncommittedEvents());
        $this->assertEquals($aggregateRootId->value, $aggregateRootValueObject->getAggregateRootId());
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
}
