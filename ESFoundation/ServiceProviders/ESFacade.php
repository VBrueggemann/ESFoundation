<?php

namespace ESFoundation\ServiceProviders;

use Illuminate\Support\Facades\Facade;


/**
 * @method static EventStore eventStore()
 * @method static EventBus eventBus()
 * @method static AggregateProjectionRepository aggregateProjectionRepository()
 * @method static QueryRepository queryRepository()
 * @method static CommandBus commandBus()
 *
 * @see \Illuminate\Log\Logger
 */
class ESFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ESF';
    }
}