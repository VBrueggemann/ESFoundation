<?php

class QueryUnitTest extends TestCase
{
    /**
     * @test
     */
    public function a_job_can_write_into_the_query_repository()
    {
        $queryRepository = $this->app->make(\ESFoundation\ES\Contracts\QueryRepository::class);
        $job = new \tests\TestJob();
        dispatch($job);
        $this->assertEquals('Data that took very long to process', $queryRepository->get('test'));
    }

    /**
     * @test
     */
    public function the_query_repository_returns_the_newest_entry()
    {
        $queryRepository = $this->app->make(\ESFoundation\ES\Contracts\QueryRepository::class);
        $job = new \tests\TestJob();
        dispatch($job);
        $job2 = new \tests\TestJob('updated data');
        dispatch($job2);
        $this->assertEquals('updated data', $queryRepository->get('test'));
    }
}
