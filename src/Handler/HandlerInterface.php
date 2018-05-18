<?php
/**
 * Created by PhpStorm.
 * User: derekcameron
 * Date: 09/02/2018
 * Time: 16:30
 */

namespace C5JapanAPI\Handler;



interface HandlerInterface
{

    public function handle();

    public function getRequestPath();



}