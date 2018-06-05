<?php

class RedisEventStoreUnitTest extends TestCase
{
    /**
     * @test
     */
    public function an_event_stream_can_be_retrieved_from_the_redis_store()
    {
        $eventStore = new \ESFoundation\ES\RedisEventStore();

        $payload = collect([
            'first' => 'one',
            'second' => 'two'
        ]);

        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $inputEvent = \tests\TestEvent::wraped($aggregateRootId, $payload);

        $eventStore->push($inputEvent);

        $stream = $eventStore->get($aggregateRootId);

        $outputEvent = $stream->first();

        $this->assertEquals($aggregateRootId->value, $outputEvent->getAggregateRootId()->value);
        $this->assertEquals($inputEvent->first()->getPayload(), $outputEvent->getPayload());
    }
}
