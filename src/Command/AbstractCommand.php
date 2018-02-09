<?php

namespace C5JapanAPI\Command;
defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Class AbstractCommand
 * This class is used for API commands to create/read/update/delete pages/users/blocks/etc
 */
abstract class AbstractCommand implements CommandInterface
{
    /** @var array $data */
    protected $data = [];
    /** @var  array $options */
    protected $options = [];

    /**
     * @param $methodName
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function __call($methodName, $params)
    {
        // Get the method prefix to determine action
        $methodPrefix = substr($methodName, 0, 3);
        // Get the attribute to do action with
        $attr = camelcase(substr($methodName, 3));

        // Check action (Set/Get)
        if ($methodPrefix == 'set' && count($params) == 1) {
            $attr = lcfirst($attr);
            // Get the parameter value
            $value = $params[0];
            // Set the value

            if (property_exists($this, $attr)) {
                echo $methodName;
                $this->$attr = $value;
            } else {
                $this->options[$attr] = $value;
            }

        } elseif ($methodPrefix == 'get') {
            // Return the value
            return $this->$attr;
        } else {
            Throw New \Exception(t('Method does not exist on %s', get_class($this)));
        }
    }


    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        $name = camelcase($name);
        $name = lcfirst($name);

            if (property_exists($this, $name)) {
                return $this->{$name};
            } else {
                return $this->options[$name];
            }
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $name = camelcase($name);
        $name = lcfirst($name);

        if (property_exists($this, $name) && $name !== 'options' && $name !== 'data') {
            $this->$name = $value;
        } elseif ($name == 'data') {
            $this->setData($value);
        } elseif ($name == 'options') {
            $this->setOptions($value);
        } else {
            $this->options[$name] = $value;
        }
    }


    /**
     * Function for manually setting data
     *
     * @param array $data
     */
    public function setData($data = [])
    {
        if (is_array($data)) {
            $this->data = $data;
        } else {
            $this->data = [$data];
        }

    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Function used to get one option
     *
     * @param $option
     * @return mixed
     */
    public function getOption($option)
    {
        return $this->options[$option];
    }

    /**
     *  Function used to set various options
     *
     * @param array $options
     */
    public function setOptions($options=[])
    {
        if (is_array($options)) {
            $this->options = array_merge($this->options, $options);
        } else {
            $this->options[] = $options;
        }

    }

    /**
     * Function used to reset all of the options on the command
     */
    public function resetOptions() {
        $this->options = [];
    }

    /**
     * @param $option
     * @param string $value
     */
    public function setOption($option, $value = '') {
        $this->options[$option] = $value;
    }

    /**
     * @param $userID
     */
    public function setUserID($userID)
    {
        $this->options['uID'] = $userID;
    }

    /**
     * @return mixed
     */
    public function getUserID()
    {
        return $this->options['uID'];
    }




}