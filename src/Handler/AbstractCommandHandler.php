<?php

namespace C5JapanAPI\Handler;


use C5JapanAPI\Command\CommandInterface;
use C5JapanAPI\Command\AbstractCommand;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Http\Request;

/**
 * The default handler for all commands without one
 *
 * Class AbstractCommandHandler
 * @package C5JapanAPI\Handler
 */
abstract class AbstractCommandHandler implements HandlerInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;
    /** @var  AbstractCommand */
    protected $command;
    /** @var Request */
    protected $request;


    /**
     * AbstractCommandHandler constructor.
     * @param $command AbstractCommand
     */
    public function __construct(CommandInterface $command)
    {
        $this->command = $command;
        $this->request = $this->app->make(Request::class);
    }


    /**
     * Function that gets select data or all the data from a request
     * @param AbstractCommand $command
     */
    public function getRequestData() {
        if ($this->request->getRealMethod() === 'GET') {
            // Return all of the Body Paramaters
            $this->parseRequestData($options = $this->request->query->all());
        } else {
            // Return all of the Body Paramaters
            $this->parseRequestData($this->request->request->all());
        }
    }

    /**
     * Parses the request data and sets the options/data
     *
     * @param array $options
     */
    public function parseRequestData($options = []) {
        if (isset($options['data'])) {
            $this->command->setData($options['data']);
        } else {
            $this->command->setData([]);
        }

        $this->command->setOptions($options);
    }


}