<?php

namespace C5JapanAPI\Handler;


use C5JapanAPI\Command\CommandInterface;
use C5JapanAPI\Command\AbstractCommand;
use C5JapanAPI\Request\RequestInterface;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Http\Request;

/**
 * The default handler for all commands without one
 *
 * Class AbstractCommandHandler
 * @package C5JapanAPI\Handler
 */
abstract class AbstractRequestHandler implements HandlerInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;
    /** @var  AbstractCommand */
    protected $command;
    /** @var RequestInterface */
    protected $request;


    /**
     * AbstractCommandHandler constructor.
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }



}