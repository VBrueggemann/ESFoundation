<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\AggregateRoot;
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

    private function __construct()
    {
    }

    public static function applyThat(DomainEventStream $domainEventStream, AggregateRootProjection $AggregateRootProjection): void
    {
        self::validate($domainEventStream, $AggregateRootProjection);
        self::represent($domainEventStream, $AggregateRootProjection);
    }

    public static function initialize(DomainEventStream $domainEventStream, bool $withValidation = false): AggregateRootProjection
    {
        $self = get_called_class();
        $className = $self . 'Values';
        $AggregateRootProjection = new $className($domainEventStream->first()->getAggregateRootId());

        if ($withValidation){
            $self::validate($domainEventStream, $AggregateRootProjection);
        }
        $self::represent($domainEventStream, $AggregateRootProjection, false);

        return $AggregateRootProjection;
    }

    public static function represent(
        DomainEventStream $domainEventStream,
        AggregateRootProjection $AggregateRootProjection,
        bool $pushToUncommittedEvents = true): void
    {
        $self = get_called_class();
        foreach ($domainEventStream as $index => $domainEvent) {
            $applyMethod = $self::getApplyMethod($domainEvent, $AggregateRootProjection);

            $AggregateRootProjection->setPlayhead($domainEvent->getPlayhead() ?: $AggregateRootProjection->getPlayhead() + 1);

            throw_if(!$self::$applyMethod($domainEvent, $AggregateRootProjection), FailedApplication::class);

            if ($pushToUncommittedEvents) {
                $domainEvent->setPlayhead($AggregateRootProjection->getPlayhead());

                $domainEvent->setAggregateRootId(new AggregateRootId($AggregateRootProjection->getAggregateRootId()));

                $AggregateRootProjection->pushToUncommittedEvents($domainEvent);
            }
        }
    }

    public static function validate(DomainEventStream $domainEventStream, AggregateRootProjection $AggregateRootProjection): void
    {
        $self = get_called_class();
        $validator = $self::getValidator();
        foreach ($domainEventStream as $index => $domainEvent) {
            throw_if($domainEventStream->guard($index), NotADomainEvent::class);
            throw_if(!method_exists($self, $self::getApplyMethod($domainEvent)), NoApplyMethod::class);
            throw_if($validator && !$validator::validate($AggregateRootProjection, $domainEvent), FailedValidation::class);
        }
    }

    protected static function getApplyMethod($domainEvent, AggregateRootProjection $AggregateRootProjection = null)
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