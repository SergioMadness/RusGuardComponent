<?php

namespace datalayerru\skud;

class EmployeeGroup
{
    public $ID;
    public $Name;
    public $Comment;
    public $IsRemoved;

    public function __construct($rawParams)
    {
        $this->ID        = $rawParams['ID'];
        $this->Name      = $rawParams['Name'];
        $this->Comment   = $rawParams['Comment'];
        $this->IsRemoved = $rawParams['IsRemoved'];
    }
}