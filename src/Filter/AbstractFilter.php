<?php
/**
 * Created by PhpStorm.
 * User: derekcameron
 * Date: 23/02/2018
 * Time: 11:40
 */

namespace C5JapanAPI\Filter;


use Concrete\Core\Support\Facade\Facade;
use Concrete\Core\Validation\SanitizeService;
use C5JapanAPI\Model\AbstractModel;

abstract class AbstractFilter extends AbstractModel implements FilterInterface
{
    /**
     *
     */
    CONST FIELDS = [
        'keywords'=>['string'],
        'parent'=>['string','int'],
        'username'=>['string'],
        'page'=>['int'],
        'attribute'=>['']
    ];
    protected $name;
    protected $function;
    protected $value;
    protected $type;
    protected $handle;
    protected $extra;

    /**
     * @return mixed
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @param mixed $handle
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;
    }

    /**
     * @return mixed
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param mixed $extra
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Sets functions based upon the name and predefined constants
     */
    public function setFunction()
    {
        if (isset($this::FIELDS[$this->name]['function'])) {
            $this->function = $this::FIELDS[$this->name]['function'];
        } else {
            $this->function = null;
        }

    }


    /**
     * AbstractFilter constructor.
     * @param $array
     * @throws \Exception
     */
    public function __construct($array)
    {
        if (is_array($array)) {
            $this->setName($array['name']);

                $this->getTypeByValue($array['value']);
                unset($array['value']);


            if ($this->name == 'attribute' && !isset($array['handle'])) {
                throw new \Exception(t('Missing Handle From Attribute Filter'));
            } else {
                $this->setHandle($array['handle']);
                unset($array['handle']);
            }
            $this->setExtra($array);
            $this->setFunction();
        } else {
            throw new \Exception(t('Expected Array got %s', $array));
        }

    }


    /**
     * @param $value
     */
    protected function getTypeByValue($value)
    {
        $app = Facade::getFacadeApplication();
        $sanitizer = $app->make(SanitizeService::class);
        foreach ($this::FIELDS as $field=>$types) {
            if ($this->name == $field) {
                if (in_array('email', $types)) {
                    $this->value = $sanitizer->sanitizeEmail($value);
                    if (!empty($this->value)) {
                        $this->setType('email');
                        break;
                    }
                }
                if (in_array('int', $types)) {
                    $this->value = $sanitizer->sanitizeInt($value);
                    if (!empty($this->value)) {
                        $this->setType('int');
                        break;
                    }
                }
                if (in_array('array', $types) && is_array($value)) {
                        $this->setType('array');
                        break;
                    }
                }
                if (in_array('url', $types)) {
                    $this->value = $sanitizer->sanitizeUrl($value);
                    if (!empty($this->value)) {
                        $this->setType('url');
                        break;
                    }

                } else {
                    $this->value = $sanitizer->sanitizeString($value);
                    $this->setType('string');
                    break;
                }


        }
    }

    /**
     * @param $value
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $app = Facade::getFacadeApplication();
        $sanitizer = $app->make(SanitizeService::class);
        if ($this->type ==='email') {
            $this->value = $sanitizer->sanitizeEmail($value);
        } elseif ($this->type ==='int') {
            $this->value = $sanitizer->sanitizeInt($value);
        } elseif ($this->type ==='url') {
            $this->value = $sanitizer->sanitizeUrl($value);
        } elseif ($this->type ==='array' && is_array($value)) {
            $this->value = $value;
        } else {
            $this->value = $sanitizer->sanitizeString($value);
        }
    }

    public function __destruct()
    {
        unset($this->value);
        unset($this->name);
        unset($this->function);
    }




}