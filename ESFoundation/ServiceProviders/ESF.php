<?php

namespace ESFoundation\ServiceProviders;

use ESFoundation\CQRS\Contracts\CommandBus;
use ESFoundation\ES\Contracts\AggregateProjectionRepository;
use ESFoundation\ES\Contracts\EventBus;
use ESFoundation\ES\Contracts\EventStore;
use ESFoundation\ES\Contracts\QueryRepository;

class ESF
{
    /**
     * @return \Laravel\Lumen\Application|mixed
     */
    public function eventStore()
    {
        return app(EventStore::class);
    }

    /**
     * @return \Laravel\Lumen\Application|mixed
     */
    public function eventBus()
    {
        return app(EventBus::class);
    }

    /**
     * @return \Laravel\Lumen\Application|mixed
     */
    public function aggregateProjectionRepository()
    {
        return app(AggregateProjectionRepository::class);
    }

    /**
     * @return \Laravel\Lumen\Application|mixed
     */
    public function queryRepository()
    {
        return app(QueryRepository::class);
    }

    /**
     * @return \Laravel\Lumen\Application|mixed
     */
    public function commandBus()
    {
        return app(CommandBus::class);
    }
}