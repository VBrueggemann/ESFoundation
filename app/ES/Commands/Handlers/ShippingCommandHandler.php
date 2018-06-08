<?php
namespace App\ES\Commands\Handlers;

use ESFoundation\CQRS\CommandHandler as CommandHandlerContract;
use ESFoundation\ES\Contracts\EventBus;
use ESFoundation\ES\Contracts\AggregateProjectionRepository;

class ShippingCommandHandler extends CommandHandlerContract
{
    private $eventBus;
    private $aggregateProjectionRepository;
    
    public function __construct(EventBus $eventBus, AggregateProjectionRepository $aggregateProjectionRepository)
    {
        $this->eventBus = $eventBus;
        $this->aggregateProjectionRepository = $aggregateProjectionRepository;
    }
            
    public function handleShipIsArriving(ShipIsArriving $command)
    {
    
    }
            
    public function handleShipIsLeaving(ShipIsLeaving $command)
    {
    
    }
}
