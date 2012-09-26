<?php

namespace Moa;
use \Moa;
use \Moa\DomainObject;

class Document
{
    private $data=array(); 

    public function __construct($data=null)
    {
        foreach ($this->properties() as $property => $type)
        {
            $type->initialise($this->data, $property);
        }

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

    public function __get($key)
    {
        if (isset($this->data[$key]) && $this->data[$key] instanceof \Moa\DomainObject\LazyProperty)
            return $this->data[$key]->get();
        return $this->data[$key];
    }

    public function __set($key, $value)
    {
        if (isset($this->data[$key]) && $this->data[$key] instanceof \Moa\DomainObject\LazyProperty)
        {
            $this->data[$key]->set($value);
        }
        else
        {
            $this->data[$key] = $value;
        }
    }

    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    public function __unset($key)
    {
        unset($this->data[$key]);
    }   
}