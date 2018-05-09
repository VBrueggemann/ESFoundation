<?php

namespace ESFoundation\CQRS;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

abstract class Command
{
    protected $payload;

    /**
     * Command constructor.
     * @param $payload
     */
    public function __construct($payload = null)
    {
        if (!is_object($payload)) {
            $payload = collect([$payload]);
        }

        if (get_class($payload) != Collection::class) {
            $payload = collect($payload);
        }

        $validator = Validator::make($payload->toArray(), is_array($this->rules()) ? $this->rules() : [$this->rules()]);
        throw_if($validator->fails(), Errors\InvalidCommandPayloadException::class, $validator->errors());

        $payload = $this->rules() ? $payload->only(collect($this->rules())->keys()) : $payload;

        $this->payload = $payload;
    }

    /**
     * @return array
     */
    protected function rules()
    {
        return [];
    }

    /**
     * @param $property
     * @return mixed
     */
    public function __get($property) {
        if ($this->payload->has($property)) {
            return $this->payload->get($property);
        }
    }

    /**
     * @return Collection|null|static
     */
    public function getPayload()
    {
        return $this->payload;
    }
}