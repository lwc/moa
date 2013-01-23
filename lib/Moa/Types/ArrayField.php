<?php

namespace Moa\Types;

use \Moa;

class ArrayField extends LazyType
{
    protected $defaultOptions = array(
        'required' => false,
        'type' => null
    );

    public static function construct($type=null)
    {
        return new ArrayField(array('type' => $type));
    }

    public function validate($value)
    {
        if (isset($value) && $value instanceof Moa\DomainObject\ArrayProperty)
        {
            $value = $value->get();
            if (isset($value))
                $value = (array)$value;
        }

        parent::validate($value);
        if (isset($value) && !is_array($value))
            $this->error('is not an array');

        $type = $this->options['type'];
        if (isset($value) && $type)
        {
            foreach ($value as $k => $item)
            {
                try
                {
                    $type->validate($item);
                }
                catch(TypeException $e)
                {
                    $this->error("element '$k' ".$e->getMessage());
                }
            }
        }
    }

    public function toMongo(&$doc, &$mongoDoc, $key)
    {
        $property = $this->initialise($doc, $key);
        $mongoDoc[$key] = $property->getIdentity();
    }

    public function fromMongo(&$doc, &$mongoDoc, $key)
    {
        $property = $this->initialise($doc, $key);
        $type = $this->options['type'];
        if (isset($mongoDoc[$key]))
        {
            $property->setIdentity($mongoDoc[$key]);
        }
    }

    public function get(&$doc, $key)
    {
        $property = $this->initialise($doc, $key);
        return $property->get();
    }

    public function set(&$doc, $key, $value)
    {
        $property = $this->initialise($doc, $key);
        $property->set($value);
    }

    public function del(&$doc, $key)
    {
        $property = $this->initialise($doc, $key);
        $property->del();
    }

    public function hasValue(&$doc, $key)
    {
        $property = $this->initialise($doc, $key);
        return $property->hasValue();
    }

    private function initialise(&$doc, $key)
    {
        if (isset($doc[$key]) && $doc[$key] instanceof Moa\DomainObject\ArrayProperty)
            return $doc[$key];

        $doc[$key] = new Moa\DomainObject\ArrayProperty($this->options['type']);
        return $doc[$key];
    }
}
