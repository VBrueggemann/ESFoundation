<?php
namespace ESFoundation\ES;

use ESFoundation\ES\Errors\InvalidEventPayloadException;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\Traits\Payloadable;
use ESFoundation\Traits\PayloadableContract;
use Ramsey\Uuid\Uuid;

abstract class DomainEvent extends StorageEvent implements PayloadableContract
{

    use Payloadable;

    protected $aggregateRootId;

    /**
     * DomainEvent constructor.
     * @param $aggregateRootId
     * @param $payload
     */
    public function __construct(AggregateRootId $aggregateRootId, $payload)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->aggregateRootId = $aggregateRootId;

        $this->setPayload($payload, InvalidEventPayloadException::class);
    }

    public function serializePayload()
    {
        return serialize($this->payload->toJson());
    }

    public static function deserializePayload(DomainStorageEvent $event)
    {
        $domainEvent = new $event->class(
            new AggregateRootId($event->getAggregateRootId()),
            collect(json_decode(unserialize($event->getPayload())))
        );
        $domainEvent->playhead = $event->getPlayhead();
        $domainEvent->createdAt = $event->getCreatedAt();
        $domainEvent->id = $event->getId();
        return $domainEvent;
    }

    /**
     * @return AggregateRootId
     */
    public function getAggregateRootId(): AggregateRootId
    {
        return $this->aggregateRootId;
    }
}