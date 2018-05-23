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
        $aggregateRepository = new \ESFoundation\ES\NonCachingAggregateRepository($eventStore);
        $eventBus = new IntegrationTestEventBus($eventStore, $aggregateRepository);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $commandHandler = new IntegrationTestCommandHandler($eventBus, $aggregateRepository);
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
            )->test
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
    private $eventBus;
    private $aggregateRepository;

    function __construct(\ESFoundation\ES\Contracts\EventBus $eventBus, \ESFoundation\ES\Contracts\AggregateRepository $aggregateRepository)
    {
        $this->aggregateRepository = $aggregateRepository;
        $this->eventBus = $eventBus;
    }

    public function handleIntegrationTestCommand(IntegrationTestCommand $command)
    {
        $testAggregateValue = $this->aggregateRepository->load(
            new AggregateRootId($command->aggregateRootId),
            IntegrationTestAggregateRoot::class
        );

        if (!$testAggregateValue) {
            $this->eventBus->dispatch(
                \ESFoundation\ES\DomainEventStream::wrap(
                    new IntegrationTestEvent(new AggregateRootId($command->aggregateRootId), ['test' => 'first'])
                )
            );
            return;
        }

        IntegrationTestAggregateRoot::applyThat(
            \ESFoundation\ES\DomainEventStream::wrap(
                new IntegrationTestEvent(null, ['test' => $command->test])
            ),
            $testAggregateValue
        );

        $this->eventBus->dispatch($testAggregateValue->popUncommittedEvents());
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
    public static function applyThatIntegrationTestEvent(IntegrationTestEvent $testEvent, \ESFoundation\ES\ValueObjects\AggregateRootValueObject $aggregateRootValueObject)
    {
        $aggregateRootValueObject->put('test', $testEvent->test);
        return true;
    }
}

class IntegrationTestAggregateRootValidator implements \ESFoundation\ES\Contracts\AggregateRootValidator
{
    public static function validate(\ESFoundation\ES\ValueObjects\AggregateRootValueObject $aggregateRoot, \ESFoundation\ES\DomainEvent $domainEvent): bool
    {
        return $aggregateRoot->test !== $domainEvent->test;
    }
}

class IntegrationTestEventBus extends \ESFoundation\ES\InMemorySynchronusEventBus {
    private $eventStore;
    private $aggregateRepository;

    public function __construct(\ESFoundation\ES\Contracts\EventStore $eventStore, \ESFoundation\ES\Contracts\AggregateRepository $aggregateRepository = null)
    {
        $this->eventStore = $eventStore;
        parent::__construct();

    }

    public function dispatch(\ESFoundation\ES\DomainEventStream $domainEventStream)
    {
        parent::dispatch($domainEventStream);
        $this->eventStore->push($domainEventStream);
    }
}

class IntegrationTestAggregateRootValues extends \ESFoundation\ES\ValueObjects\AggregateRootValueObject {

    public static function valueObjects(): \Illuminate\Support\Collection
    {
        return collect([
           'test' => IntegrationTestValueObject::class
        ]);
    }
}

class IntegrationTestValueObject extends \ESFoundation\ValueObjects\ValueObject {

    public static function rules(): string
    {
        return \Illuminate\Validation\Rule::in(['first', 'second']);
    }
}