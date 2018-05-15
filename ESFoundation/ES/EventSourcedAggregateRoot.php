<?php

namespace ESFoundation\ES;

use ESFoundation\ES\ValueObjects\AggregateRootId;

abstract class EventSourcedAggregateRoot implements AggregateRoot
{
    private $uncommittedEvents;
    private $aggregateRootId;

    protected function __construct(AggregateRootId $aggregateRootId, DomainEvent $creationEvent = null)
    {
        $this->aggregateRootId = $aggregateRootId;
        $this->uncommittedEvents = collect();

        if ($creationEvent) {
            $this->applyThat(DomainEventStream::wrap($creationEvent));
        }
    }

    public function applyThat(DomainEventStream $domainEventStream)
    {
        $validator = $this->getValidator();
        $hasErrors = false;
        
        foreach ($domainEventStream as $index => $domainEvent) {
            $applyMethod = $this->getApplyMethod($domainEvent);
            if (!method_exists($this, $applyMethod)) {
                break;
            }

            if ($validator && !$validator::validate($this, $domainEvent)) {
                break;
            }

            $hasErrors = $hasErrors || $this->$applyMethod($domainEvent);

            $this->uncommittedEvents->push($domainEvent);
        }

        return $hasErrors;
    }

    public function popUncommittedEvents()
    {
        $domainEventStream = new DomainEventStream($this->uncommittedEvents);

        $this->uncommittedEvents = [];

        return $domainEventStream;
    }

    public static function initialize(DomainEventStream $domainEventStream)
    {
        $className = get_called_class();
        $aggregateRoot = new $className($domainEventStream->first()->getAggregateRootId());

        foreach ($domainEventStream as $domainEvent) {

            $applyMethod = $aggregateRoot->getApplyMethod($domainEvent);

            if (!method_exists($aggregateRoot, $applyMethod)) {
                break;
            }

            $aggregateRoot->$applyMethod($domainEvent);
        }

        return $aggregateRoot;
    }

    protected function getApplyMethod($event)
    {
        $classParts = explode('\\', get_class($event));

        return 'applyThat' . end($classParts);
    }

    protected function getValidator()
    {
        $validator = get_class($this) . 'Validator';

        if (!(class_exists($validator) && isset(class_implements($validator)['ESFoundation\ES\AggregateRootValidator']))) {
            return null;
        }

        return $validator;
    }

    public function getAggregateRootId()
    {
        return $this->aggregateRootId;
    }
}