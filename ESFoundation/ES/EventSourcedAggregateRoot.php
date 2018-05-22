<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\AggregateRoot;
use ESFoundation\ES\Errors\FailedApplication;
use ESFoundation\ES\Errors\FailedValidation;
use ESFoundation\ES\Errors\NoApplyMethod;
use ESFoundation\ES\Errors\NotADomainEvent;
use ESFoundation\ES\ValueObjects\AggregateRootId;

abstract class EventSourcedAggregateRoot implements AggregateRoot
{
    private $uncommittedEvents;
    private $aggregateRootId;
    private $playhead = -1;
    protected $handleMethods = [];

    protected function __construct(AggregateRootId $aggregateRootId)
    {
        $this->aggregateRootId = $aggregateRootId->value;
        $this->uncommittedEvents = collect();
    }

    public function applyThat(DomainEventStream $domainEventStream): bool
    {
        $this->validate($domainEventStream);
        return $this->represent($domainEventStream);
    }

    public function popUncommittedEvents(): DomainEventStream
    {
        $domainEventStream = DomainEventStream::wrap($this->uncommittedEvents);

        $this->uncommittedEvents = collect();

        return $domainEventStream;
    }

    public static function initialize(DomainEventStream $domainEventStream, bool $withValidation = false): AggregateRoot
    {
        $className = get_called_class();
        $aggregateRoot = new $className($domainEventStream->first()->getAggregateRootId());

        if ($withValidation){
            $aggregateRoot->validate($domainEventStream);
        }
        $aggregateRoot->represent($domainEventStream, true);

        return $aggregateRoot;
    }

    public function represent(DomainEventStream $domainEventStream, bool $pushToUncommittedEvents = false)
    {
        foreach ($domainEventStream as $index => $domainEvent) {
            $applyMethod = $this->getApplyMethod($domainEvent);

            $this->playhead = $this->playhead + 1;

            throw_if(!$this->$applyMethod($domainEvent), FailedApplication::class);

            if (!$pushToUncommittedEvents) {
                $domainEvent->setPlayhead($this->playhead);

                $domainEvent->setAggregateRootId(new AggregateRootId($this->aggregateRootId));

                $this->uncommittedEvents->push($domainEvent);
            }
        }
        return true;
    }

    protected function validate(DomainEventStream $domainEventStream)
    {
        $validator = $this->getValidator();
        foreach ($domainEventStream as $index => $domainEvent) {
            throw_if($domainEventStream->guard($index), NotADomainEvent::class);
            throw_if(!method_exists($this, $this->getApplyMethod($domainEvent)), NoApplyMethod::class);
            throw_if($validator && !$validator::validate($this, $domainEvent), FailedValidation::class);
        }
    }

    protected function getApplyMethod($event)
    {
        if (array_key_exists(get_class($event), $this->handleMethods)) {
            return $this->handleMethods[get_class($event)];
        }

        $classParts = explode('\\', get_class($event));

        return 'applyThat' . end($classParts);
    }

    protected function getValidator()
    {
        $validator = get_class($this) . 'Validator';

        if (!(class_exists($validator) && isset(class_implements($validator)['ESFoundation\ES\Contracts\AggregateRootValidator']))) {
            return null;
        }

        return $validator;
    }

    public function getAggregateRootId(): string
    {
        return $this->aggregateRootId;
    }

    /**
     * @return mixed
     */
    public function getPlayhead(): int
    {
        return $this->playhead;
    }
}