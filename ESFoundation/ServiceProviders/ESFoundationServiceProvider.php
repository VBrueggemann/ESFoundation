<?php

namespace ESFoundation\ServiceProviders;

use ESFoundation\Console\CreateAggregateRoot;
use ESFoundation\Console\CreateAggregateRootProjection;
use ESFoundation\Console\CreateAggregateRootValidator;
use ESFoundation\Console\CreateCommand;
use ESFoundation\Console\CreateCommandHandler;
use ESFoundation\Console\CreateEvent;
use ESFoundation\CQRS\Contracts\CommandBus;
use ESFoundation\CQRS\InMemorySynchronusCommandBus;
use ESFoundation\ES\Contracts\AggregateProjectionRepository;
use ESFoundation\ES\Contracts\EventBus;
use ESFoundation\ES\Contracts\EventStore;
use ESFoundation\ES\Contracts\QueryRepository;
use ESFoundation\ES\InMemoryCachingAggregateProjectionRepository;
use ESFoundation\ES\InMemoryNonAtomicEventStore;
use ESFoundation\ES\InMemoryQueryRepository;
use ESFoundation\ES\InMemorySynchronusEventBus;
use ESFoundation\ES\RedisCachingAggregateProjectionRepository;
use ESFoundation\ES\RedisEventBus;
use ESFoundation\ES\RedisEventStore;
use ESFoundation\ES\RedisQueryRepository;
use Illuminate\Support\ServiceProvider;

class ESFoundationServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            CreateCommandHandler::class,
            CreateCommand::class,
            CreateAggregateRoot::class,
            CreateEvent::class,
            CreateAggregateRootValidator::class,
            CreateAggregateRootProjection::class
        ]);

        $this->app->singleton(QueryRepository::class, function ($app) {
            switch (env('QUERY_REPOSITORY', 'memory')) {
                case 'memory': return new InMemoryQueryRepository();
                case 'redis': return new RedisQueryRepository();
            }
        });

        $this->app->singleton(EventStore::class, function ($app) {
            switch (env('EVENT_STORE', 'memory')) {
                case 'memory': return new InMemoryNonAtomicEventStore();
                case 'redis': return new RedisEventStore();
            }
        });

        $this->app->singleton(AggregateProjectionRepository::class, function ($app) {
            switch (env('AGGREGATE_REPOSITORY', 'memory')) {
                case 'memory': return new InMemoryCachingAggregateProjectionRepository($app->make(EventStore::class));
                case 'redis': return new RedisCachingAggregateProjectionRepository($app->make(EventStore::class));
            }
        });

        $this->app->singleton(EventBus::class, function ($app) {
            switch (env('EVENT_BUS', 'memory')) {
                case 'memory': return new InMemorySynchronusEventBus($app->make(EventStore::class));
                case 'redis': return new RedisEventBus($app->make(EventStore::class));
            }
        });

        $this->app->singleton(CommandBus::class, function ($app) {
            switch (env('COMMAND_BUS', 'memory')) {
                case 'memory': return new InMemorySynchronusCommandBus();
            }
        });

        $this->app->bind('ESF', function ($app) {
            return new ESF();
        });

        if (!class_exists('ESF')) {
            class_alias('ESFoundation\ServiceProviders\ESFacade', 'ESF');
        }
    }

    public function boot()
    {
        // $this->app->make(EventBus::class)->subscribe($this->app->make(AggregateProjectionRepository::class));
    }
}