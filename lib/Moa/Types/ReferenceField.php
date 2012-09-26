<?php

namespace Moa\Types;

use \Moa;

class ReferenceField extends Type
{
    protected $defaultOptions = array(
        'required' => false,
        'type' => null
    );

    public function initialise(&$doc, $key)
    {
        $doc[$key] = new Moa\DomainObject\ReferenceProperty($this->options['type']);
    }

    public function validate($value)
    {
        parent::validate($value);
        $type = $this->options['type'];
        $value = $value->get();
        if (isset($value) && !$value instanceof $type)
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
        if (isset($doc[$key]) && $doc[$key]->hasValue())
        {
            $identity = $doc[$key]->getIdentity();
            $mongoDoc[$key] = $identity['id'];
            $mongoDoc[$key.'__type'] = $identity['type'];
        }
        else
            $mongoDoc[$key] = null;
    }

    public function fromMongo(&$doc, &$mongoDoc, $key)
    {
        $this->initialise($doc, $key);
        if (isset($mongoDoc[$key]))
        {
            $doc[$key]->setIdentity(array(
                'id' => $mongoDoc[$key],
                'type' => $mongoDoc[$key.'__type']
            ));
        }
    }
}
