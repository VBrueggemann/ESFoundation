<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\AggregateRoot;
use ESFoundation\ES\Errors\AggregateMismatch;
use ESFoundation\ES\Errors\FailedApplication;
use ESFoundation\ES\Errors\FailedValidation;
use ESFoundation\ES\Errors\NoApplyMethod;
use ESFoundation\ES\Errors\NotADomainEvent;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\AggregateRootProjection;

abstract class EventSourcedAggregateRoot implements AggregateRoot
{
    protected static $handleMethods = [];
    protected static $validator;
    private $aggregateRootProjection;

    private function __construct(AggregateRootProjection $aggregateRootProjection)
    {
        $this->aggregateRootProjection = $aggregateRootProjection;
    }

    /**
     * @param AggregateRootProjection $aggregateRootProjection
     * @return EventSourcedAggregateRoot
     */
    public static function applyOn(AggregateRootProjection $aggregateRootProjection): EventSourcedAggregateRoot
    {
        $self = get_called_class();
        return new $self($aggregateRootProjection);
    }

    public function that(DomainEventStream $domainEventStream): EventSourcedAggregateRoot
    {
        self::applyThat($domainEventStream, $this->aggregateRootProjection);
        return $this;
    }

    public static function applyThat(DomainEventStream $domainEventStream, AggregateRootProjection $aggregateRootProjection): void
    {
        self::validate($domainEventStream, $aggregateRootProjection);
        self::represent($domainEventStream, $aggregateRootProjection);
    }

    public static function initialize(DomainEventStream $domainEventStream, bool $withValidation = false): AggregateRootProjection
    {
        $self = get_called_class();
        $className = $self . 'Values';
        $aggregateRootProjection = new $className($domainEventStream->first()->getAggregateRootId());

        if ($withValidation){
            $self::validate($domainEventStream, $aggregateRootProjection);
        }
        $self::represent($domainEventStream, $aggregateRootProjection, false);

        return $aggregateRootProjection;
    }

    public static function represent(
        DomainEventStream $domainEventStream,
        AggregateRootProjection $aggregateRootProjection,
        bool $pushToUncommittedEvents = true): void
    {
        $self = get_called_class();
        foreach ($domainEventStream as $index => $domainEvent) {
            $applyMethod = $self::getApplyMethod($domainEvent, $aggregateRootProjection);

            $aggregateRootProjection->setPlayhead($domainEvent->getPlayhead() ?: $aggregateRootProjection->getPlayhead() + 1);

            throw_if(!$self::$applyMethod($domainEvent, $aggregateRootProjection), FailedApplication::class);

            if ($pushToUncommittedEvents) {
                $domainEvent->setPlayhead($aggregateRootProjection->getPlayhead());

                $domainEvent->setAggregateRootId(new AggregateRootId($aggregateRootProjection->getAggregateRootId()));

                $aggregateRootProjection->pushToUncommittedEvents($domainEvent);
            }
        }
    }

    public static function validate(DomainEventStream $domainEventStream, AggregateRootProjection $aggregateRootProjection): void
    {
        $self = get_called_class();
        $validator = $self::getValidator();
        foreach ($domainEventStream as $index => $domainEvent) {
            throw_if($domainEventStream->guard($index), NotADomainEvent::class);
            throw_if($domainEvent->getAggregateRootId() && $domainEvent->getAggregateRootId() != $aggregateRootProjection->getAggregateRootId(), AggregateMismatch::class);
            throw_if(!method_exists($self, $self::getApplyMethod($domainEvent)), NoApplyMethod::class);
            throw_if($validator && !$validator::validate($aggregateRootProjection, $domainEvent), FailedValidation::class);
        }
    }

    protected static function getApplyMethod($domainEvent, AggregateRootProjection $aggregateRootProjection = null)
    {
        $self = get_called_class();
        if (array_key_exists(get_class($domainEvent), $self::$handleMethods)) {
            return $self::$handleMethods[get_class($domainEvent)];
        }

        $classParts = explode('\\', get_class($domainEvent));

        return 'applyThat' . end($classParts);
    }

    protected static function getValidator()
    {
        $self = get_called_class();
        if ($self::$validator) {
            return $self::$validator;
        }

        $validator = $self . 'Validator';

        if (!(class_exists($validator) && isset(class_implements($validator)['ESFoundation\ES\Contracts\AggregateRootValidator']))) {
            return null;
        }

        return $validator;
    }
}