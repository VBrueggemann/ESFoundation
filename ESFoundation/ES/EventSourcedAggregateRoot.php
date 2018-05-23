<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\AggregateRoot;
use ESFoundation\ES\Errors\FailedApplication;
use ESFoundation\ES\Errors\FailedValidation;
use ESFoundation\ES\Errors\NoApplyMethod;
use ESFoundation\ES\Errors\NotADomainEvent;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\AggregateRootValueObject;

abstract class EventSourcedAggregateRoot implements AggregateRoot
{
    protected static $handleMethods = [];
    protected static $validator;

    private function __construct()
    {
    }

    public static function applyThat(DomainEventStream $domainEventStream, AggregateRootValueObject $aggregateRootValueObject): void
    {
        self::validate($domainEventStream, $aggregateRootValueObject);
        self::represent($domainEventStream, $aggregateRootValueObject);
    }

    public static function initialize(DomainEventStream $domainEventStream, bool $withValidation = false): AggregateRootValueObject
    {
        $self = get_called_class();
        $className = $self . 'Values';
        $aggregateRootValueObject = new $className($domainEventStream->first()->getAggregateRootId());

        if ($withValidation){
            $self::validate($domainEventStream, $aggregateRootValueObject);
        }
        $self::represent($domainEventStream, $aggregateRootValueObject, false);

        return $aggregateRootValueObject;
    }

    public static function represent(
        DomainEventStream $domainEventStream,
        AggregateRootValueObject $aggregateRootValueObject,
        bool $pushToUncommittedEvents = true): void
    {
        $self = get_called_class();
        foreach ($domainEventStream as $index => $domainEvent) {
            $applyMethod = $self::getApplyMethod($domainEvent, $aggregateRootValueObject);

            $aggregateRootValueObject->setPlayhead($domainEvent->getPlayhead() ?: $aggregateRootValueObject->getPlayhead() + 1);

            throw_if(!$self::$applyMethod($domainEvent, $aggregateRootValueObject), FailedApplication::class);

            if ($pushToUncommittedEvents) {
                $domainEvent->setPlayhead($aggregateRootValueObject->getPlayhead());

                $domainEvent->setAggregateRootId(new AggregateRootId($aggregateRootValueObject->getAggregateRootId()));

                $aggregateRootValueObject->pushToUncommittedEvents($domainEvent);
            }
        }
    }

    public static function validate(DomainEventStream $domainEventStream, AggregateRootValueObject $aggregateRootValueObject): void
    {
        $self = get_called_class();
        $validator = $self::getValidator();
        foreach ($domainEventStream as $index => $domainEvent) {
            throw_if($domainEventStream->guard($index), NotADomainEvent::class);
            throw_if(!method_exists($self, $self::getApplyMethod($domainEvent)), NoApplyMethod::class);
            throw_if($validator && !$validator::validate($aggregateRootValueObject, $domainEvent), FailedValidation::class);
        }
    }

    protected static function getApplyMethod($domainEvent, AggregateRootValueObject $aggregateRootValueObject = null)
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