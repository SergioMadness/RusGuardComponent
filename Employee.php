<?php

namespace datalayerru\skud;

class Employee
{
    public $ID;
    public $FirstName;
    public $LastName;
    public $IsLocked;
    public $IsRemoved;

    public function __construct($rawParams)
    {
        $this->ID        = $rawParams->ID;
        $this->FirstName = $rawParams->FirstName;
        $this->LastName  = $rawParams->LastName;
        $this->IsLocked  = $rawParams->IsLocked;
        $this->IsRemoved = $rawParams->IsRemoved;
    }
}