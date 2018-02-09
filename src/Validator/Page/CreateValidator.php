<?php

namespace C5JapanAPI\Validator\Page;


use C5JapanAPI\Command\CommandInterface;
use C5JapanAPI\Validator\ValidatorInterface;

/**
 * Class CreateValidator
 * @package C5JapanAPI\Validator\Page
 */
class CreateValidator implements ValidatorInterface
{


    /**
     * @param CommandInterface $command
     * @return null
     */
    public function validate(CommandInterface $command)
    {
        return null;
    }



}