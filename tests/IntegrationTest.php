<?php

use ESFoundation\ES\ValueObjects\AggregateRootId;

class IntegrationTest extends TestCase
{
    /**
     * @test
     */
    public function a_command_can_change_state()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateRepository = new \ESFoundation\ES\NonCashingAggregateRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $commandHandler = new IntegrationTestCommandHandler($aggregateRepository, $eventStore);
        $commandBus->subscribe($commandHandler, IntegrationTestCommand::class);
        $aggregateRootId = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $commandBus->dispatch(
            new IntegrationTestCommand([
                'aggregateRootId' => $aggregateRootId,
                'test' => 'first'
            ])
        );

        $commandBus->dispatch(
            new IntegrationTestCommand([
                'aggregateRootId' => $aggregateRootId,
                'test' => 'second'
            ])
        );

        $this->assertEquals(
            'second',
            $aggregateRepository->load(
                new AggregateRootId($aggregateRootId),
                IntegrationTestAggregateRoot::class
            )->getTest()
        );

        $this->assertEquals(
            $aggregateRootId,
            $aggregateRepository->load(
                new AggregateRootId($aggregateRootId),
                IntegrationTestAggregateRoot::class
            )->getAggregateRootId()
        );

        $this->assertEmpty(
            $aggregateRepository->load(
                new AggregateRootId($aggregateRootId),
                IntegrationTestAggregateRoot::class
            )->popUncommittedEvents()
        );

        $this->assertEquals(
            1,
            $aggregateRepository->load(
                new AggregateRootId($aggregateRootId),
                IntegrationTestAggregateRoot::class
            )->getPlayhead()
        );

        $this->assertEquals(
            0,
            $aggregateRepository->load(
                new AggregateRootId($aggregateRootId),
                IntegrationTestAggregateRoot::class,
                1
            )->getPlayhead()
        );

    }
}

class IntegrationTestCommandHandler extends \ESFoundation\CQRS\CommandHandler
{
    private $aggregateRepository;
    private $eventStore;

    function __construct(\ESFoundation\ES\AggregateRepository $aggregateRepository, \ESFoundation\ES\EventStore $eventStore)
    {
        $this->aggregateRepository = $aggregateRepository;
        $this->eventStore = $eventStore;
    }

    public function handleIntegrationTestCommand(IntegrationTestCommand $command)
    {
        $testAggregate = $this->aggregateRepository->load(
            new AggregateRootId($command->aggregateRootId),
            IntegrationTestAggregateRoot::class
        );

        if (!$testAggregate) {
            $this->eventStore->push(
                \ESFoundation\ES\DomainEventStream::wrap(
                    new IntegrationTestEvent(new AggregateRootId($command->aggregateRootId), ['test' => 'first'])
                )
            );
            return;
        }

        $testAggregate->applyThat(
            \ESFoundation\ES\DomainEventStream::wrap(
                new IntegrationTestEvent(null, ['test' => $command->test])
            )
        );

        $this->eventStore->push(
            $testAggregate->popUncommittedEvents()
        );
    }
}

class IntegrationTestCommand extends \ESFoundation\CQRS\Command
{
    public function rules()
    {
        return [
            'aggregateRootId' => 'required|' . AggregateRootId::rules(),
            'test' => 'required|string',
        ];
    }
}

class IntegrationTestEvent extends \ESFoundation\ES\DomainEvent
{
    public function rules()
    {
        return [
            'test' => 'required|string',
        ];
    }
}

class IntegrationTestAggregateRoot extends \ESFoundation\ES\EventSourcedAggregateRoot
{
    private $test = '';

    public function applyThatIntegrationTestEvent(IntegrationTestEvent $testEvent)
    {
        $this->test = $testEvent->test;
    }

    /**
     * @return string
     */
    public function getTest(): string
    {
        return $this->test;
    }
}

class IntegrationTestAggregateRootValidator implements \ESFoundation\ES\AggregateRootValidator
{
    public static function validate(\ESFoundation\ES\AggregateRoot $aggregateRoot, \ESFoundation\ES\DomainEvent $domainEvent)
    {
        return $aggregateRoot->getTest() !== $domainEvent->getPayload()['test'];
    }
}
