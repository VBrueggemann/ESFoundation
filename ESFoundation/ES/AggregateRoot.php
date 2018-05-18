<?php

namespace ESFoundation\ES;

interface AggregateRoot
{
    public function applyThat(DomainEventStream $event);

    public function popUncommittedEvents();

    public function getAggregateRootId();

    public function getPlayhead();

    public static function initialize(DomainEventStream $domainEventStream);
}