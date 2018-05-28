<?php

namespace ESFoundation\ServiceProviders;

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
        $this->app->singleton(QueryRepository::class, function ($app) {
            switch (env('QUERY_REPOSITORY', 'memory')) {
                case 'memory': return new InMemoryQueryRepository();
            }
        });

        $this->app->singleton(EventStore::class, function ($app) {
            switch (env('EVENT_STORE', 'memory')) {
                case 'memory': return new InMemoryNonAtomicEventStore();
            }
        });

        $this->app->singleton(AggregateProjectionRepository::class, function ($app) {
            switch (env('AGGREGATE_REPOSITORY', 'memory')) {
                case 'memory': return new InMemoryCachingAggregateProjectionRepository($app->make(EventStore::class));
            }
        });

        $this->app->singleton(EventBus::class, function ($app) {
            switch (env('EVENT_BUS', 'memory')) {
                case 'memory': return new InMemorySynchronusEventBus($app->make(EventStore::class));
            }
        });

        $this->app->singleton(CommandBus::class, function ($app) {
            switch (env('COMMAND_BUS', 'memory')) {
                case 'memory': return new InMemorySynchronusCommandBus();
            }
        });
    }

    public function boot()
    {
        // $this->app->make(EventBus::class)->subscribe($this->app->make(AggregateProjectionRepository::class));
    }
}