<?php

class AggregateRepositoryUnitTest extends TestCase
{
    /**
     * @test
     */
    public function an_aggregate_repository_can_retrieve_an_aggregate()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateRepository = new \ESFoundation\ES\InMemoryCashingAggregateRepository($eventStore);

        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $eventStore->push(\ESFoundation\ES\DomainEventStream::wrap(new \tests\TestEvent(
            $aggregateRootId,
            ['test' => 'test']
        )));

        $this->assertEquals(
            $aggregateRepository->load($aggregateRootId, \tests\EventSourcedTestAggregateRoot::class)->getAggregateRootId(),
            $aggregateRootId->value
        );
    }

    /**
     * @test
     */
    public function an_aggregate_repository_returns_null_if_no_aggregate_exists()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateRepository = new \ESFoundation\ES\InMemoryCashingAggregateRepository($eventStore);

        $aggregateRootId = new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString());

        $this->assertNull(
            $aggregateRepository->load($aggregateRootId, \tests\EventSourcedTestAggregateRoot::class)
        );
    }
}