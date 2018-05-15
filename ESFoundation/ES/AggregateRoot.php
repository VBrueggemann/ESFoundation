<?php

namespace ESFoundation\ES;

interface AggregateRoot
{
    public function applyThat(DomainEventStream $event);

    public function popUncommittedEvents();

    public function getAggregateRootId();
}