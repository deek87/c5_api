<?php

namespace C5JapanAPI\Commands;


use Concrete\Core\Support\Facade\Application;

/**
 * Class AbstractCommand
 * This class is used for creating commands to create/read/delete/update pages/users/etc
 */
abstract class AbstractCommand
{

    /**
     * @var array
     */
    protected $data;
    /** @var Application */
    protected $app;

    /**
     * AbstractCommand constructor.
     * @param $data array
     */
    public function __construct($data = [])
    {
        $this->data = $data;
        $this->app = Application::getFacadeApplication();
    }

    /**
     * This function is called by send() in controllers
     *
     * @return mixed
     */
    public function execute()
    {
        return $this->data;
    }


}