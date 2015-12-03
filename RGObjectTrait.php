<?php

namespace datalayerru\skud;

trait RGObjectTrait
{
    private $rawParams;

    public function __construct(array $rawParams)
    {
        $this->rawParams = $rawParams;
    }

    public function __get($name)
    {
        $result = null;

        if ($this->rawParams !== null && array_key_exists($name, $this->rawParams)) {
            $result = $this->rawParams[$name];
        }

        return $result;
    }
}