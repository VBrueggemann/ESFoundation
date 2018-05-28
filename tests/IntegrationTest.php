<?php

use ESFoundation\ES\ValueObjects\AggregateRootId;

class IntegrationTest extends TestCase
{
    /**
     * @test
     */
    public function a_command_can_change_state()
    {
        $aggregateProjectionRepository = ESF::aggregateProjectionRepository();
        $eventBus = ESF::eventBus();
        $eventBus->subscribe($aggregateProjectionRepository);
        $commandBus = ESF::commandBus();
        $commandHandler = new IntegrationTestCommandHandler($eventBus, $aggregateProjectionRepository);
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
            $aggregateProjectionRepository->load(
                new AggregateRootId($aggregateRootId),
                IntegrationTestAggregateRoot::class
            )->test
        );

        $this->assertEquals(
            $aggregateRootId,
            $aggregateProjectionRepository->load(
                new AggregateRootId($aggregateRootId),
                IntegrationTestAggregateRoot::class
            )->getAggregateRootId()
        );

        $this->assertEmpty(
            $aggregateProjectionRepository->load(
                new AggregateRootId($aggregateRootId),
                IntegrationTestAggregateRoot::class
            )->popUncommittedEvents()
        );

        $this->assertEquals(
            1,
            $aggregateProjectionRepository->load(
                new AggregateRootId($aggregateRootId),
                IntegrationTestAggregateRoot::class
            )->getPlayhead()
        );

        $this->assertEquals(
            0,
            $aggregateProjectionRepository->load(
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
    private $aggregateProjectionRepository;

    function __construct(\ESFoundation\ES\Contracts\EventBus $eventBus, \ESFoundation\ES\Contracts\AggregateProjectionRepository $aggregateProjectionRepository)
    {
        $this->aggregateProjectionRepository = $aggregateProjectionRepository;
        $this->eventBus = $eventBus;
    }

    public function handleIntegrationTestCommand(IntegrationTestCommand $command)
    {
        $testAggregateValue = $this->aggregateProjectionRepository->load(
            new AggregateRootId($command->aggregateRootId),
            IntegrationTestAggregateRoot::class
        );

        if (!$testAggregateValue) {
            $this->eventBus->dispatch(
                IntegrationTestEvent::wraped(new AggregateRootId($command->aggregateRootId), ['test' => 'first'])
            );
            return true;
        }

        IntegrationTestAggregateRoot::applyOn($testAggregateValue)->that(IntegrationTestEvent::wraped(null, ['test' => $command->test]));

//        IntegrationTestAggregateRoot::applyThat(IntegrationTestEvent::wraped(null, ['test' => $command->test]), $testAggregateValue);

        $this->eventBus->dispatch($testAggregateValue->popUncommittedEvents());
        return true;
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
    public static function applyThatIntegrationTestEvent(IntegrationTestEvent $testEvent, \ESFoundation\ES\ValueObjects\AggregateRootProjection $AggregateRootProjection)
    {
        $AggregateRootProjection->put('test', $testEvent->test);
        return true;
    }
}

class IntegrationTestAggregateRootValidator implements \ESFoundation\ES\Contracts\AggregateRootValidator
{
    public static function validate(\ESFoundation\ES\ValueObjects\AggregateRootProjection $aggregateRoot, \ESFoundation\ES\DomainEvent $domainEvent): bool
    {
        return $aggregateRoot->test !== $domainEvent->test;
    }
}

class IntegrationTestAggregateRootValues extends \ESFoundation\ES\ValueObjects\AggregateRootProjection {

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