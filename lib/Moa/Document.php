<?php

namespace Moa;
use \Moa;
use \Moa\DomainObject;

class Document
{
    private $data=array();

    public function __construct($data=null)
    {
        if (is_array($data))
        {
            foreach ($data as $key => $value)
            {
                $this->__set($key, $value);
            }
        }
    }

    public function fromMongo($mongoDoc)
    {
        $this->data = $mongoDoc;
        foreach ($this->properties() as $property => $type)
        {
            $type->fromMongo($this->data, $mongoDoc, $property);
        }
        return $this;
    }

    public function toMongo()
    {
        $mongoDoc = $this->data;
        foreach ($this->properties() as $property => $type)
        {
            $type->toMongo($this->data, $mongoDoc, $property);
        }
        return $mongoDoc;
    }

    public function validate()
    {
        foreach ($this->properties() as $property => $type)
        {
            try
            {
                $type->validate(isset($this->data[$property]) ? $this->data[$property] : null);
            }
            catch (Moa\Types\TypeException $e)
            {
                throw new Moa\DomainObject\ValidationException(
                    get_class($this).'::'.$property.' '.$e->getMessage()
                );
            }
        }
    }

    public function properties()
    {
        return array();
    }

    private function property($key)
    {
        $properties = $this->properties();
        if (array_key_exists($key, $properties))
            return $properties[$key];
    }

    public function __get($key)
    {
        $property = $this->property($key);
        if ($property && $property->isLazy())
            return $property->get($this->data, $key);
        return $this->data[$key];
    }

    public function __set($key, $value)
    {
        $property = $this->property($key);
        if ($property && $property->isLazy())
            return $property->set($this->data, $key , $value);
        return $this->data[$key] = $value;
    }

    public function __isset($key)
    {
        $property = $this->property($key);
        if ($property && $property->isLazy())
            return $property->hasValue($this->data, $key);
        return isset($this->data[$key]);
    }

    public function __unset($key)
    {
        $property = $this->property($key);
        if ($property && $property->isLazy())
            return $property->del($this->data, $key);
        unset($this->data[$key]);
    }
}