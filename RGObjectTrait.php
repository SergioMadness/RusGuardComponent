<?php

namespace datalayerru\skud;

trait RGObjectTrait
{
    private $rawParams;

    public function __construct($rawParams)
    {
        $this->rawParams = $rawParams;
    }

    public function __get($name)
    {
        $result = null;

        if ($this->rawParams !== null && property_exists($this->rawParams, $name)) {
            $result = $this->rawParams->$name;
        }

        return $result;
    }
}