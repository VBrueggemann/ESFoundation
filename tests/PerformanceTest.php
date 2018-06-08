<?php

use ESFoundation\ES\ValueObjects\AggregateRootId;

class PerformanceTest //extends TestCase
{
    /**
     * test
     */
    public function performance_single_aggregate_no_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateProjectionRepository = new \ESFoundation\ES\NonCachingAggregateProjectionRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $eventBus = new \ESFoundation\ES\InMemorySynchronusEventBus($eventStore);
        $commandHandler = new PerformanceTestCommandHandler($eventBus, $aggregateProjectionRepository);
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
                $aggregateProjectionRepository->load(
                    new AggregateRootId($aggregateRootId),
                    PerformanceTestAggregateRoot::class
                );

                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_single_aggregate_no_caching_integration_test.csv', (($i/10) . ',' . (microtime(true) - $t) . ',' . $i*10));
            }
        }
    }

    /**
     * @test
     */
    public function performance_single_aggregate_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateProjectionRepository = new \ESFoundation\ES\InMemoryCachingAggregateProjectionRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $eventBus = new \ESFoundation\ES\InMemorySynchronusEventBus($eventStore);
        $commandHandler = new PerformanceTestCommandHandler($eventBus, $aggregateProjectionRepository);
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

            $aggregateProjectionRepository->load(
                new AggregateRootId($aggregateRootId),
                PerformanceTestAggregateRoot::class
            );

            if ($i%10 == 0) {
                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_single_aggregate_caching_integration_test.csv', (($i/10) . ',' . (microtime(true) - $t) . ',' . $i*10));
            }
        }
    }

    /**
     * @test
     */
    public function performance_redis_event_store_single_aggregate_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\RedisEventStore();
        $aggregateProjectionRepository = new \ESFoundation\ES\InMemoryCachingAggregateProjectionRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $eventBus = new \ESFoundation\ES\InMemorySynchronusEventBus($eventStore);
        $commandHandler = new PerformanceTestCommandHandler($eventBus, $aggregateProjectionRepository);
        $commandBus->subscribe($commandHandler, PerformanceTestCommand::class);
        $aggregateRootId = \Ramsey\Uuid\Uuid::uuid4()->toString();

        \Illuminate\Support\Facades\Storage::disk('local')->put('performance_redis_event_store_single_aggregate_caching_integration_test.csv', 'nr,time,events');

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

            $aggregateProjectionRepository->load(
                new AggregateRootId($aggregateRootId),
                PerformanceTestAggregateRoot::class
            );

            if ($i%10 == 0) {
                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_redis_event_store_single_aggregate_caching_integration_test.csv', (($i/10) . ',' . (microtime(true) - $t) . ',' . $i*10));
            }
        }
    }

    /**
     * @test
     */
    public function performance_multiple_aggregate_no_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateProjectionRepository = new \ESFoundation\ES\NonCachingAggregateProjectionRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $eventBus = new \ESFoundation\ES\InMemorySynchronusEventBus($eventStore);
        $commandHandler = new PerformanceTestCommandHandler($eventBus, $aggregateProjectionRepository);
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
                    $aggregateProjectionRepository->load(
                        new AggregateRootId($agri),
                        PerformanceTestAggregateRoot::class
                    );
                }

                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_multiple_aggregate_no_caching_integration_test.csv', (($i/10) . ',' . (microtime(true) - $t) . ',' . $i*10));
            }
        }
    }

    /**
     * @test
     */
    public function performance_multiple_aggregate_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateProjectionRepository = new \ESFoundation\ES\InMemoryCachingAggregateProjectionRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $eventBus = new \ESFoundation\ES\InMemorySynchronusEventBus($eventStore);
        $commandHandler = new PerformanceTestCommandHandler($eventBus, $aggregateProjectionRepository);
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
                    $aggregateProjectionRepository->load(
                        new AggregateRootId($agri),
                        PerformanceTestAggregateRoot::class
                    );
                }

                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_multiple_aggregate_caching_integration_test.csv', (($i/10) . ',' . (microtime(true) - $t) . ',' . $i*10));
            }
        }
    }


     /**
     * @test
     */
    public function performance_redis_event_store_multiple_aggregate_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\RedisEventStore();
        $aggregateProjectionRepository = new \ESFoundation\ES\InMemoryCachingAggregateProjectionRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $eventBus = new \ESFoundation\ES\InMemorySynchronusEventBus($eventStore);
        $commandHandler = new PerformanceTestCommandHandler($eventBus, $aggregateProjectionRepository);
        $commandBus->subscribe($commandHandler, PerformanceTestCommand::class);

        \Illuminate\Support\Facades\Storage::disk('local')->put('performance_redis_event_store_multiple_aggregate_caching_integration_test.csv', 'nr,time,events');

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
                    $aggregateProjectionRepository->load(
                        new AggregateRootId($agri),
                        PerformanceTestAggregateRoot::class
                    );
                }

                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_redis_event_store_multiple_aggregate_caching_integration_test.csv', (($i/10) . ',' . (microtime(true) - $t) . ',' . $i*10));
            }
        }
    }

    /**
     * @test
     */
    public function performance_multiple_aggregate_one_event_no_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateProjectionRepository = new \ESFoundation\ES\NonCachingAggregateProjectionRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $eventBus = new \ESFoundation\ES\InMemorySynchronusEventBus($eventStore);
        $commandHandler = new PerformanceTestCommandHandler($eventBus, $aggregateProjectionRepository);
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
                    $aggregateProjectionRepository->load(
                        new AggregateRootId($agid),
                        PerformanceTestAggregateRoot::class
                    );
                }

                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_multiple_aggregate_one_event_no_caching_integration_test.csv', (($i/100) . ',' . (microtime(true) - $t) . ',' . $i));
            }
        }
    }

    /**
     * @test
     */
    public function performance_multiple_aggregate_one_event_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\InMemoryNonAtomicEventStore();
        $aggregateProjectionRepository = new \ESFoundation\ES\InMemoryCachingAggregateProjectionRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $eventBus = new \ESFoundation\ES\InMemorySynchronusEventBus($eventStore);
        $commandHandler = new PerformanceTestCommandHandler($eventBus, $aggregateProjectionRepository);
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
                    $aggregateProjectionRepository->load(
                        new AggregateRootId($agid),
                        PerformanceTestAggregateRoot::class
                    );
                }

                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_multiple_aggregate_one_event_caching_integration_test.csv', (($i/100) . ',' . (microtime(true) - $t) . ',' . $i));
            }
        }
    }

    /**
     * @test
     */
    public function performance_redis_event_store_multiple_aggregate_one_event_caching_integration_test()
    {
        $eventStore = new \ESFoundation\ES\RedisEventStore();
        $aggregateProjectionRepository = new \ESFoundation\ES\InMemoryCachingAggregateProjectionRepository($eventStore);
        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();
        $eventBus = new \ESFoundation\ES\InMemorySynchronusEventBus($eventStore);
        $commandHandler = new PerformanceTestCommandHandler($eventBus, $aggregateProjectionRepository);
        $commandBus->subscribe($commandHandler, PerformanceTestCommand::class);

        \Illuminate\Support\Facades\Storage::disk('local')->put('performance_redis_event_store_multiple_aggregate_one_event_caching_integration_test.csv', 'nr,time,events');

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
                    $aggregateProjectionRepository->load(
                        new AggregateRootId($agid),
                        PerformanceTestAggregateRoot::class
                    );
                }

                \Illuminate\Support\Facades\Storage::disk('local')
                    ->append('performance_redis_event_store_multiple_aggregate_one_event_caching_integration_test.csv', (($i/100) . ',' . (microtime(true) - $t) . ',' . $i));
            }
        }
    }
}

class PerformanceTestCommandHandler extends \ESFoundation\CQRS\CommandHandler
{
    private $eventBus;
    private $aggregateProjectionRepository;

    function __construct(\ESFoundation\ES\Contracts\EventBus $eventBus, \ESFoundation\ES\Contracts\AggregateProjectionRepository $aggregateProjectionRepository)
    {
        $this->aggregateProjectionRepository = $aggregateProjectionRepository;
        $this->eventBus = $eventBus;
    }

    public function handlePerformanceTestCommand(PerformanceTestCommand $command)
    {
        $testAggregateValue = $this->aggregateProjectionRepository->load(
            new AggregateRootId($command->aggregateRootId),
            PerformanceTestAggregateRoot::class
        );

        if (!$testAggregateValue) {
            $this->eventBus->dispatch(
                PerformanceTestEvent::wraped(new AggregateRootId($command->aggregateRootId), ['test' => $command->test])
            );
            return true;
        }

        PerformanceTestAggregateRoot::applyOn($testAggregateValue)->that(PerformanceTestEvent::wraped(null, ['test' => $command->test]));

        $this->eventBus->dispatch($testAggregateValue->popUncommittedEvents());
        return true;
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
    public static function applyThatPerformanceTestEvent(PerformanceTestEvent $testEvent, \ESFoundation\ES\ValueObjects\AggregateRootProjection $aggregateRootProjection)
    {
        $aggregateRootProjection->put('test', $testEvent->test);
        return true;
    }
}

class PerformanceTestAggregateRootValidator implements \ESFoundation\ES\Contracts\AggregateRootValidator
{
    public static function validate(\ESFoundation\ES\ValueObjects\AggregateRootProjection $aggregateRoot, \ESFoundation\ES\DomainEvent $domainEvent): bool
    {
        return $aggregateRoot->test !== $domainEvent->test;
    }
}

class PerformanceTestAggregateRootProjection extends \ESFoundation\ES\ValueObjects\AggregateRootProjection {

    public static function valueObjects(): \Illuminate\Support\Collection
    {
        return collect([
            'test' => PerformanceTestValueObject::class
        ]);
    }
}

class PerformanceTestValueObject extends \ESFoundation\ValueObjects\ValueObject {

    public static function rules(): string
    {
        return 'integer';
    }
}
