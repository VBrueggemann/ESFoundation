<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\QueryRepository;

class InMemoryQueryRepository implements QueryRepository
{
    private $queries = [];

    function __construct()
    {
    }

    public function add($key, $query)
    {
        if (array_key_exists($key, $this->queries)) {
            $this->queries[$key][sizeof($this->queries[$key])] = $query;
            return;
        }
        $this->queries[$key][0] = $query;
    }

    public function get($key, $index = -1)
    {
        if (!key_exists($key, $this->queries)) {
            return [];
        }
        if ($index < 0) {
            return [array_last($this->queries[$key])];
        }

        return array_reverse($this->queries[$key])[$index];
    }
}