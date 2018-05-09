<?php

namespace tests;

use ESFoundation\CQRS\Command;

class TestCommand extends Command
{
    private $rules = [];

    public function __construct($payload = null, $rules = [])
    {
        $this->rules = $rules;
        parent::__construct($payload);
    }

    protected function rules()
    {
        return $this->rules;
    }
}