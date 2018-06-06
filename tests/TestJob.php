<?php

namespace tests;

use App\Jobs\Job;
use ESFoundation\ES\Contracts\QueryRepository;

class TestJob extends Job
{
    private $data;

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function handle(QueryRepository $queryRepository)
    {
        $queryRepository->add('test', $this->data ?: 'Data that took very long to process');
    }
}