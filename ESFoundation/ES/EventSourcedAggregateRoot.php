<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\AggregateRoot;
use ESFoundation\ES\ValueObjects\AggregateRootId;

abstract class EventSourcedAggregateRoot implements AggregateRoot
{
    private $uncommittedEvents;
    private $aggregateRootId;
    private $playhead = -1;

    protected function __construct(AggregateRootId $aggregateRootId, DomainEvent $creationEvent = null)
    {
        $this->aggregateRootId = $aggregateRootId->value;
        $this->uncommittedEvents = collect();

        if ($creationEvent) {
            $this->applyThat(DomainEventStream::wrap($creationEvent));
        }
    }

    public function applyThat(DomainEventStream $domainEventStream): bool
    {
        $validator = $this->getValidator();
        $hasNoErrors = true;
        
        foreach ($domainEventStream as $index => $domainEvent) {
            if ($domainEventStream->guard($index)) {
                $hasNoErrors = false;
                break;
            }

            $applyMethod = $this->getApplyMethod($domainEvent);
            if (!method_exists($this, $applyMethod)) {
                $hasNoErrors = false;
                break;
            }

            if ($validator && !$validator::validate($this, $domainEvent)) {
                $hasNoErrors = false;
                break;
            }

            $this->playhead = $this->playhead + 1;

            $domainEvent->setPlayhead($this->playhead);

            $domainEvent->setAggregateRootId(new AggregateRootId($this->aggregateRootId));

            $hasNoErrors = $hasNoErrors && $this->$applyMethod($domainEvent);

            $this->uncommittedEvents->push($domainEvent);
        }

        return $hasNoErrors;
    }

    public function popUncommittedEvents(): DomainEventStream
    {
        $domainEventStream = DomainEventStream::wrap($this->uncommittedEvents);

        $this->uncommittedEvents = collect();

        return $domainEventStream;
    }

    public static function initialize(DomainEventStream $domainEventStream): AggregateRoot
    {
        $className = get_called_class();
        $aggregateRoot = new $className($domainEventStream->first()->getAggregateRootId());

        foreach ($domainEventStream as $domainEvent) {

            $applyMethod = $aggregateRoot->getApplyMethod($domainEvent);

            if (!method_exists($aggregateRoot, $applyMethod)) {
                break;
            }

            $aggregateRoot->playhead = $aggregateRoot->playhead + 1;

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