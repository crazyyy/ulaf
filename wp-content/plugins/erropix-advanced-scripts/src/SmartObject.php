<?php

namespace ERROPiX\AdvancedScripts;

use ArrayAccess;

class SmartObject implements ArrayAccess
{
    private $deep;

    public function __construct($data = [], $deep = false)
    {
        $this->deep = $deep;

        if (is_array($data)) {
            foreach ($data as $name => $value) {
                $this->__set($name, $value);
            }
        }
    }

    public function set($name, $value)
    {
        if ($this->deep && is_array($value)) {
            $value = new self($value);
        }
        $this->$name = $value;
    }

    public function get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return null;
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name)
    {
        return property_exists($this, $name);
    }

    public function __unset($name)
    {
        unset($this->$name);
    }

    public function __call($name, $arguments)
    {
        return null;
    }

    static function __set_state($data = [])
    {
        return new self($data);
    }

    public function __toString()
    {
        return print_r(get_object_vars($this), true);
    }

    /* ArrayAccess Methods */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}
