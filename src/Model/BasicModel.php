<?php

namespace C5JapanAPI\Model;


class BasicModel extends AbstractModel
{
    protected $data = [];
    protected $dataClass = '\C5JapanAPI\Model\BasicModel';
    protected $dataType = 'class';


    public function __construct($array = [])
    {
        if (is_array($array) && !empty($array)) {
            $this->mapData($array);
        }
    }
}