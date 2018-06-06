<?php

namespace ESFoundation\ES;

use ESFoundation\ES\Contracts\QueryRepository;
use Illuminate\Support\Facades\Redis;

class RedisQueryRepository implements QueryRepository
{
    public function add($key, $query)
    {
        $redis = Redis::connection('queries');
        $redis->lpush($key, [$query]);
    }

    public function get($key, $index = -1)
    {
        $redis = Redis::connection('queries');

        if ($index < 0) {
            return $redis->lrange($key, 0, 0);
        }

        return $redis->lrange($key, 0, $index);
    }
}