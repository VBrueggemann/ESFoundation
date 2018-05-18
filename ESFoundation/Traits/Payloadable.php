<?php
namespace ESFoundation\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

trait Payloadable
{
    protected $payload;

    public function rules()
    {
        return [];
    }

    private function setPayload($payload, $errorClass)
    {
        if (is_array($payload)) {
            $payload = collect($payload);
        }

        if (!is_object($payload)) {
            $payload = collect([$payload]);
        }

        if (get_class($payload) != Collection::class) {
            $payload = collect($payload);
        }

        $validator = Validator::make($payload->toArray(), is_array($this->rules()) ? $this->rules() : [$this->rules()]);
        throw_if($validator->fails(), $errorClass, $validator->errors());

        $payload = $this->rules() ? $payload->only(collect($this->rules())->keys()) : $payload;

        $this->payload = $payload;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
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
}