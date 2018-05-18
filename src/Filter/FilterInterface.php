<?php
/**
 * Created by PhpStorm.
 * User: derekcameron
 * Date: 23/02/2018
 * Time: 14:40
 */

namespace C5JapanAPI\Filter;


interface FilterInterface
{
    public function setName($filterName);
    public function filter(&$list);
    public function setValue($value);

}