<?php
namespace App\ES\Events\Aggregates;

use ESFoundation\ES\EventSourcedAggregateRoot as AggregateRootContract;
use ESFoundation\ES\ValueObjects\AggregateRootProjection;

class AggregateRoot extends AggregateRootContract
{
    
     public function applyThatShipHasArrived(ShipHasArrived $event, AggregateRootProjection $aggregateRootProjection)
     {
     
     }
     
     public function applyThatShipHasLeft(ShipHasLeft $event, AggregateRootProjection $aggregateRootProjection)
     {
     
     }
     
}