<?php

class RedisEventBusUnitTest // extends TestCase
{

    public function tearDown()
    {
        parent::tearDown();
        $redis = \Illuminate\Support\Facades\Redis::connection();
        $redis->flushdb();
    }

    /**
     * @test
     */
    public function an_event_can_be_dispatched_on_a_event_bus()
    {
        $eventBus = new \ESFoundation\ES\RedisEventBus();
        $eventListener = new \tests\TestRedisEventListener();
        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $eventBus->subscribe($eventListener, $aggregateRootId);

        $event = new \tests\TestEvent($aggregateRootId, ['test' => 'test']);

        sleep(4);

        $eventBus->dispatch(ESFoundation\ES\DomainEventStream::wrap($event));

        sleep(4);

        $this->assertEquals(get_class($event), get_class($eventListener->getHandledEvents($aggregateRootId)->first()));
        $this->assertEquals($event->getId(), ($eventListener->getHandledEvents($aggregateRootId)->first()->getId()));
    }

    /**
     * @test
     */
    public function an_event_handler_can_be_subscribed_to_every_event()
    {
        $eventBus = new \ESFoundation\ES\RedisEventBus();
        $eventListener = new \tests\TestRedisEventListener();
        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $eventBus->subscribe($eventListener);

        $event = new \tests\TestEvent($aggregateRootId, ['test' => 'test']);

        sleep(4);

        $eventBus->dispatch(ESFoundation\ES\DomainEventStream::wrap($event));

        sleep(4);

        $this->assertEquals(get_class($event), get_class($eventListener->getHandledEvents($aggregateRootId)->first()));
        $this->assertEquals($event->getId(), ($eventListener->getHandledEvents($aggregateRootId)->first()->getId()));
    }
}
