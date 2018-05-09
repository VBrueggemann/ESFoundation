<?php

namespace ESFoundation\Traits;

interface PayloadableContract
{
    public function rules();
    
    public function getPayload();
}