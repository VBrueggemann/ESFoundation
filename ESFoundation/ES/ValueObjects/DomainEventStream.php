<?php

namespace ESFoundation\ES;

use Illuminate\Support\Collection;

class DomainEventStream extends Collection
{
    public function guard($index)
    {
        return $this->items[$index] instanceof DomainEvent;
    }
}