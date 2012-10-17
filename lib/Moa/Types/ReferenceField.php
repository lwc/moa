<?php

namespace Moa\Types;

use \Moa;

class ReferenceField extends LazyType
{
    protected $defaultOptions = array(
        'required' => false,
        'type' => null
    );

    public function validate($value)
    {
        $value = $value->get();
        parent::validate($value);

        $type = $this->options['type'];
        if (isset($value) && $type && !$value instanceof $type)
            $this->error('is not an instance of '.$type);
        if (isset($value) && !$value->saved())
        {
            try
            {
                $value->validate();
            }
            catch (Moa\DomainObject\ValidationException $e)
            {
                $this->error('failed validation with message "'.$e->getMessage().'"');
            }
        }
    }

    public function toMongo(&$doc, &$mongoDoc, $key)
    {
        $property = $this->initialise($doc, $key);
        if ($property->hasValue())
        {
            $identity = $property->getIdentity();
            $mongoDoc[$key] = $identity['id'];
            $mongoDoc[$key.'__type'] = $identity['type'];
        }
        else
        {
            $mongoDoc[$key] = null;
            $mongoDoc[$key.'__type'] = null;
        }
    }

    public function fromMongo(&$doc, &$mongoDoc, $key)
    {
        $property = $this->initialise($doc, $key);
        if (isset($mongoDoc[$key]))
        {
            $property->setIdentity(array(
                'id' => $mongoDoc[$key],
                'type' => $mongoDoc[$key.'__type']
            ));
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
        if (!isset($doc[$key]))
            $doc[$key] = new Moa\DomainObject\ReferenceProperty();
        return $doc[$key];
    }    
}
