<?php

namespace C5JapanAPI\Methods;


use C5JapanAPI\Command\CommandInterface;
use C5JapanAPI\Handler\HandlerInterface;

interface MethodInterface
{

    public function validate();
    public function handle();
    public function validateToken();
    public function validateCommand();
    public function setCommand(CommandInterface $command);
    public function setHandler(HandlerInterface $handler);


}