<?php

namespace ESFoundation\ES\Contracts;

interface QueryRepository
{
    public function add($key, $query);

    public function get($key, $index = -1);
}