<?php
/**
 * Created by PhpStorm.
 * User: derekcameron
 * Date: 09/02/2018
 * Time: 16:30
 */

namespace C5JapanAPI\Handler;


use C5JapanAPI\Command\CommandInterface;

interface HandlerInterface
{

    public function __construct(CommandInterface $command);

    function handle();

    public function getRequestData();
    public function parseRequestData();


}