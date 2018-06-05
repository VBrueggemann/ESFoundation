<?php

class EventBusUnitTest extends TestCase
{
    /**
     * @test
     */
    public function an_event_can_be_dispatched_on_a_event_bus()
    {
        $eventBus = new \ESFoundation\ES\InMemorySynchronusEventBus();
        $eventListener = new \tests\TestEventListener();
        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $eventBus->subscribe($eventListener, $aggregateRootId);

        $event = new \tests\TestEvent($aggregateRootId, ['test' => 'test']);
        $eventBus->dispatch(ESFoundation\ES\DomainEventStream::wrap($event));

        $this->assertEquals(get_class($event), $eventListener->getHandledEvents()[0]);
    }

    /**
     * @test
     */
    public function an_event_handler_can_be_subscribed_to_every_event()
    {
        $eventBus = new \ESFoundation\ES\InMemorySynchronusEventBus();
        $eventListener = new \tests\TestEventListener();
        $eventBus->subscribe($eventListener);

        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $event = new \tests\TestEvent($aggregateRootId, ['test' => 'test']);
        $eventBus->dispatch(ESFoundation\ES\DomainEventStream::wrap($event));

        $this->assertEquals(get_class($event), $eventListener->getHandledEvents()[0]);
    }

    /**
     * @test
     */
    public function an_event_handler_can_be_subscribed_to_specific_aggregates()
    {
        $eventBus = new \ESFoundation\ES\InMemorySynchronusEventBus();
        $eventListener = new \tests\TestEventListener();

        $aggregateRootIdOther = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $eventBus->subscribe($eventListener, $aggregateRootIdOther);

        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $event = new \tests\TestEvent($aggregateRootId, ['test' => 'test']);
        $eventBus->dispatch(ESFoundation\ES\DomainEventStream::wrap($event));

        $this->assertEmpty($eventListener->getHandledEvents());
    }
}
