<?php
namespace ESFoundation\ES;

use ESFoundation\ES\Errors\InvalidEventPayloadException;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\DomainEventId;
use ESFoundation\Traits\Payloadable;
use ESFoundation\Traits\PayloadableContract;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;

abstract class DomainEvent extends StorageEvent implements PayloadableContract
{

    use Payloadable;

    protected $aggregateRootId;

    /**
     * DomainEvent constructor.
     * @param AggregateRootId $aggregateRootId
     * @param Carbon $payload
     * @param int $playhead
     * @param DomainEventId $id
     * @param Carbon $createdAt
     * @internal param $int $
     */
    public function __construct(AggregateRootId $aggregateRootId, $payload, int $playhead = 0, DomainEventId $id = null, Carbon $createdAt = null)
    {
        $this->id = $id ?? $this->id = Uuid::uuid4()->toString();
        $this->aggregateRootId = $aggregateRootId;

        $this->setPayload($payload, InvalidEventPayloadException::class);

        $this->playhead = $playhead;
        $this->createdAt = $createdAt;
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