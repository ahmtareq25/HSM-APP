<?php

namespace App\Services;

class ResourceContainer
{

    private $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function getResource(){
        return $this->resource;
    }

}
