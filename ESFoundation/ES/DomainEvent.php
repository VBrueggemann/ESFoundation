<?php
namespace ESFoundation\ES;

use ESFoundation\ES\Errors\InvalidEventPayloadException;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\DomainEventId;
use ESFoundation\Traits\Payloadable;
use ESFoundation\Traits\PayloadableContract;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;
use Serializable;

abstract class DomainEvent extends StorageEvent implements PayloadableContract
{

    use Payloadable;

    protected $aggregateRootId;

    /**
     * DomainEvent constructor.
     * @param AggregateRootId $aggregateRootId
     * @param $payload
     * @param int $playhead
     * @param DomainEventId $id
     * @param Carbon $createdAt
     * @internal param $int $
     */
    public function __construct(
        AggregateRootId $aggregateRootId = null,
        $payload = null,
        int $playhead = 0,
        DomainEventId $id = null,
        Carbon $createdAt = null
    )
    {
        $this->id = $id ?: new DomainEventId(Uuid::uuid4()->toString());
        $this->aggregateRootId = $aggregateRootId;

        $this->setPayload($payload, InvalidEventPayloadException::class);

        $this->playhead = $playhead;
        $this->createdAt = $createdAt;
    }

    public function serializePayload()
    {
        return $this->payload->toArray();
    }

    public function serialize(bool $withAggregateRootId = false)
    {
        return serialize((new DomainStorageEvent(
            $this->aggregateRootId,
            $this->id,
            $this->createdAt ?: Carbon::now(),
            $this->playhead,
            $this->serializePayload(),
            get_class($this)
        ))->toJson($withAggregateRootId));
    }

    public static function deserializePayload(DomainStorageEvent $event)
    {
        return (new $event->class(
            $event->getAggregateRootId(),
            $event->getPayload(),
            $event->getPlayhead(),
            $event->getId(),
            $event->getCreatedAt()
        ));
    }

    /**
     * @return AggregateRootId
     */
    public function getAggregateRootId(): ?AggregateRootId
    {
        return $this->aggregateRootId;
    }

    /**
     * @param AggregateRootId $aggregateRootId
     */
    public function setAggregateRootId(AggregateRootId $aggregateRootId)
    {
        $this->aggregateRootId = $this->aggregateRootId ?? $aggregateRootId;
    }

    public static function wraped(  AggregateRootId $aggregateRootId = null,
                                    $payload = null,
                                    int $playhead = 0,
                                    DomainEventId $id = null,
                                    Carbon $createdAt = null)
    {
        $self = get_called_class();
        return DomainEventStream::wrap(new $self($aggregateRootId, $payload, $playhead, $id, $createdAt));
    }
}