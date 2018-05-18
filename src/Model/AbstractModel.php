<?php

namespace C5JapanAPI\Model;


/**
 * AbstractModel used for Making objects
 *
 * Class AbstractModel
 * @package C5JapanAPI\Model
 */
abstract class AbstractModel implements ModelInterface
{

    /**
     * @var array
     */
    protected $modelData = [];

    /**
     * Initialize this object's properties from an array.
     *
     * @param $array array Used to seed this object's properties.
     * @return void
     */
    final public function mapData($array)
    {
        foreach ($array as $key => $val) {

            $keyType = $this->keyClass($key);
            if ($keyType !== null) {
                $dataType = $this->keyType($key);
                if ($dataType == 'array' || $dataType == 'map') {
                    $this->$key = [];

                    foreach ($val as $itemKey => $itemVal) {
                        // Check if it is already a
                        if ($itemVal instanceof $keyType) {
                            $this->{$key}[$itemKey] = $itemVal;
                        } else {
                            $this->{$key}[$itemKey] = new $keyType($itemVal);
                        }
                    }
                } elseif ($val instanceof $keyType) {
                    $this->$key = $val;
                } else {
                    $this->$key = new $keyType($val);
                }
                unset($array[$key]);
            } elseif (property_exists($this, $key)) {
                $this->$key = $val;
                unset($array[$key]);
            } elseif (property_exists($this, $camelKey = $this->camelCase($key))) {
                // This checks if property exists as camelCase, leaving it in array as snake_case
                // in case of backwards compatibility issues.
                $this->$camelKey = $val;
            }
        }
    }

    /**
     * Function to get the matching class with the data
     *
     * @param $key
     * @return string | null
     */
    protected function keyClass($key)
    {
        $keyType = $key . "Class";
        // ensure that the the key is a valid class
        if (property_exists($this, $keyType) && class_exists($this->$keyType)) {
            return $this->$keyType;
        } else {
            return null;
        }
    }

    /**
     * Checks the key to see if it is an array or class
     *
     * @param $key
     * @return mixed
     */
    protected function keyType($key)
    {
        $dataType = $key . "Type";
        if (property_exists($this, $dataType)) {
            return $this->$dataType;
        }
    }

    /**
     * Convert a string to camelCase
     * @param  string $value
     * @return string
     */
    protected function camelCase($value)
    {
        $value = ucwords(str_replace(array('-', '_'), ' ', $value));
        $value = str_replace(' ', '', $value);
        $value[0] = strtolower($value[0]);
        return $value;
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
        } elseif (isset($this->modelData[$name])) {
            return $this->modelData[$name];
        } else {
            return null;
        }
    }
}