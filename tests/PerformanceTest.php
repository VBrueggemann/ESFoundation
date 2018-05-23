<?php

use ESFoundation\ES\ValueObjects\AggregateRootId;

class PerformanceTest extends TestCase
{
    /**
     * test
     */
    public function performance_single_aggregate_no_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateRepository = new \ESFoundation\ES\NonCachingAggregateRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $commandHandler = new PerformanceTestCommandHandler($aggregateRepository, $eventStore);
        $commandBus->subscribe($commandHandler, PerformanceTestCommand::class);
        $aggregateRootId = \Ramsey\Uuid\Uuid::uuid4()->toString();

        \Illuminate\Support\Facades\Storage::disk('local')->put('performance_single_aggregate_no_caching_integration_test.csv', 'nr,time,events');

        for ($i = 0; $i<1500; $i++) {
            $t = microtime(true);
            for ($j = 0; $j<10; $j++) {
                $commandBus->dispatch(
                    new PerformanceTestCommand([
                        'aggregateRootId' => $aggregateRootId,
                        'test' => $i + $j
                    ])
                );
            }

            if ($i%10 == 0) {
                $aggregateRepository->load(
                    new AggregateRootId($aggregateRootId),
                    IntegrationTestAggregateRoot::class
                );

                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_single_aggregate_no_caching_integration_test.csv', (($i/10) . ',' . (microtime(true) - $t) . ',' . $i*10));
            }
        }
    }

    /**
     * test
     */
    public function performance_single_aggregate_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateRepository = new \ESFoundation\ES\InMemoryCachingAggregateRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $commandHandler = new PerformanceTestCommandHandler($aggregateRepository, $eventStore);
        $commandBus->subscribe($commandHandler, PerformanceTestCommand::class);
        $aggregateRootId = \Ramsey\Uuid\Uuid::uuid4()->toString();

        \Illuminate\Support\Facades\Storage::disk('local')->put('performance_single_aggregate_caching_integration_test.csv', 'nr,time,events');

        for ($i = 0; $i<1500; $i++) {
            $t = microtime(true);
            for ($j = 0; $j<10; $j++) {
                $commandBus->dispatch(
                    new PerformanceTestCommand([
                        'aggregateRootId' => $aggregateRootId,
                        'test' => $i + $j
                    ])
                );
            }

            $aggregateRepository->load(
                new AggregateRootId($aggregateRootId),
                IntegrationTestAggregateRoot::class
            );

            if ($i%10 == 0) {
                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_single_aggregate_caching_integration_test.csv', (($i/10) . ',' . (microtime(true) - $t) . ',' . $i*10));
            }
        }
    }

    /**
     * test
     */
    public function performance_multiple_aggregate_no_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateRepository = new \ESFoundation\ES\NonCachingAggregateRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $commandHandler = new PerformanceTestCommandHandler($aggregateRepository, $eventStore);
        $commandBus->subscribe($commandHandler, PerformanceTestCommand::class);

        \Illuminate\Support\Facades\Storage::disk('local')->put('performance_multiple_aggregate_no_caching_integration_test.csv', 'nr,time,events');

        for ($i = 0; $i<1500; $i++) {
            $t = microtime(true);

            $aggregateRootId[$i] = \Ramsey\Uuid\Uuid::uuid4()->toString();

            for ($j = 0; $j<10; $j++) {
                $commandBus->dispatch(
                    new PerformanceTestCommand([
                        'aggregateRootId' => $aggregateRootId[$i],
                        'test' => $i + $j
                    ])
                );
            }

            if ($i%10 == 0) {
                foreach ($aggregateRootId as $agri){
                    $aggregateRepository->load(
                        new AggregateRootId($agri),
                        IntegrationTestAggregateRoot::class
                    );
                }

                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_multiple_aggregate_no_caching_integration_test.csv', (($i/10) . ',' . (microtime(true) - $t) . ',' . $i*10));
            }
        }
    }

    /**
     * test
     */
    public function performance_multiple_aggregate_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateRepository = new \ESFoundation\ES\InMemoryCachingAggregateRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $commandHandler = new PerformanceTestCommandHandler($aggregateRepository, $eventStore);
        $commandBus->subscribe($commandHandler, PerformanceTestCommand::class);

        \Illuminate\Support\Facades\Storage::disk('local')->put('performance_multiple_aggregate_caching_integration_test.csv', 'nr,time,events');

        for ($i = 0; $i<1500; $i++) {
            $t = microtime(true);

            $aggregateRootId[$i] = \Ramsey\Uuid\Uuid::uuid4()->toString();

            for ($j = 0; $j<10; $j++) {
                $commandBus->dispatch(
                    new PerformanceTestCommand([
                        'aggregateRootId' => $aggregateRootId[$i],
                        'test' => $i + $j
                    ])
                );
            }

            if ($i%10 == 0) {
                foreach ($aggregateRootId as $agri){
                    $aggregateRepository->load(
                        new AggregateRootId($agri),
                        IntegrationTestAggregateRoot::class
                    );
                }

                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_multiple_aggregate_caching_integration_test.csv', (($i/10) . ',' . (microtime(true) - $t) . ',' . $i*10));
            }
        }
    }

    /**
     * test
     */
    public function performance_multiple_aggregate_one_event_no_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateRepository = new \ESFoundation\ES\NonCachingAggregateRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $commandHandler = new PerformanceTestCommandHandler($aggregateRepository, $eventStore);
        $commandBus->subscribe($commandHandler, PerformanceTestCommand::class);

        \Illuminate\Support\Facades\Storage::disk('local')->put('performance_multiple_aggregate_one_event_no_caching_integration_test.csv', 'nr,time,events');

        for ($i = 0; $i<15000; $i++) {
            $t = microtime(true);
            $aggregateRootId[$i] = \Ramsey\Uuid\Uuid::uuid4()->toString();

            $commandBus->dispatch(
                new PerformanceTestCommand([
                    'aggregateRootId' => $aggregateRootId[$i],
                    'test' => $i + $i
                ])
            );

            if ($i%100 == 0) {
                foreach ($aggregateRootId as $agid) {
                    $aggregateRepository->load(
                        new AggregateRootId($agid),
                        IntegrationTestAggregateRoot::class
                    );
                }

                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_multiple_aggregate_one_event_no_caching_integration_test.csv', (($i/100) . ',' . (microtime(true) - $t) . ',' . $i));
            }
        }
    }

    /**
     * test
     */
    public function performance_multiple_aggregate_one_event_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateRepository = new \ESFoundation\ES\InMemoryCachingAggregateRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $commandHandler = new PerformanceTestCommandHandler($aggregateRepository, $eventStore);
        $commandBus->subscribe($commandHandler, PerformanceTestCommand::class);

        \Illuminate\Support\Facades\Storage::disk('local')->put('performance_multiple_aggregate_one_event_caching_integration_test.csv', 'nr,time,events');

        for ($i = 0; $i<15000; $i++) {
            $t = microtime(true);
            $aggregateRootId[$i] = \Ramsey\Uuid\Uuid::uuid4()->toString();

            $commandBus->dispatch(
                new PerformanceTestCommand([
                    'aggregateRootId' => $aggregateRootId[$i],
                    'test' => $i + $i
                ])
            );

            if ($i%100 == 0) {
                foreach ($aggregateRootId as $agid) {
                    $aggregateRepository->load(
                        new AggregateRootId($agid),
                        IntegrationTestAggregateRoot::class
                    );
                }

                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_multiple_aggregate_one_event_caching_integration_test.csv', (($i/100) . ',' . (microtime(true) - $t) . ',' . $i));
            }
        }
    }
}

class PerformanceTestCommandHandler extends \ESFoundation\CQRS\CommandHandler
{
    private $aggregateRepository;
    private $eventStore;

    function __construct(\ESFoundation\ES\AggregateRepository $aggregateRepository, \ESFoundation\ES\EventStore $eventStore)
    {
        $this->aggregateRepository = $aggregateRepository;
        $this->eventStore = $eventStore;
    }

    public function handlePerformanceTestCommand(PerformanceTestCommand $command)
    {
        $testAggregate = $this->aggregateRepository->load(
            new AggregateRootId($command->aggregateRootId),
            PerformanceTestAggregateRoot::class
        );

        if (!$testAggregate) {
            $this->eventStore->push(
                \ESFoundation\ES\DomainEventStream::wrap(
                    new PerformanceTestEvent(new AggregateRootId($command->aggregateRootId), ['test' => $command->test])
                )
            );
            return;
        }

        $testAggregate->applyThat(
            \ESFoundation\ES\DomainEventStream::wrap(
                new PerformanceTestEvent(null, ['test' => $command->test])
            )
        );

        $this->eventStore->push(
            $testAggregate->popUncommittedEvents()
        );
    }
}

class PerformanceTestCommand extends \ESFoundation\CQRS\Command
{
    public function rules()
    {
        return [
            'aggregateRootId' => 'required|' . AggregateRootId::rules(),
            'test' => 'required|integer',
        ];
    }
}

class PerformanceTestEvent extends \ESFoundation\ES\DomainEvent
{
    public function rules()
    {
        return [
            'test' => 'required|integer',
        ];
    }
}

class PerformanceTestAggregateRoot extends \ESFoundation\ES\EventSourcedAggregateRoot
{
    private $test = '';

    public function applyThatPerformanceTestEvent(PerformanceTestEvent $testEvent)
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

class PerformanceTestAggregateRootValidator implements \ESFoundation\ES\Contracts\AggregateRootValidator
{
    public static function validate(\ESFoundation\ES\ValueObjects\AggregateRootValueObject $aggregateRoot, \ESFoundation\ES\DomainEvent $domainEvent): bool
    {
        return $aggregateRoot->getTest() !== $domainEvent->getPayload()['test'];
    }
}
