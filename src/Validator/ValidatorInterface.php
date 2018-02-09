<?php
namespace C5JapanAPI\Validator;

use Concrete\Core\Error\ErrorList\ErrorList;
use C5JapanAPI\Command\CommandInterface;

interface ValidatorInterface
{

    /**
     * @param CommandInterface $command
     * @return ErrorList
     */
    public function validate(CommandInterface $command);

}
