<?php

namespace Moa\DomainObject;
use \Moa;

class ReferenceProperty implements Moa\DomainObject\LazyProperty
{
    private
        $id,
        $type,
        $instance
        ;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function getIdentity()
    {
        if (isset($this->instance))
        {
            if (!$this->instance->saved())
                $this->instance->save();

            $this->id = $this->instance->id();
            $this->type = get_class($this->instance);
        }

        return array(
            'id' => $this->id,
            'type' => $this->type
        );
    }

    public function setIdentity($identity)
    {
        $this->id = $identity['id'];
        $this->type = $identity['type'];
        $this->instance = null;
    }

    public function hasValue()
    {
        return ($this->id || $this->instance);
    }

    public function get()
    {
        if ($this->instance)
            return $this->instance;

        if ($this->id == null)
            return null;

        return $this->instance = $this->loadModel();
    }

    public function set($instance)
    {
        $this->instance = $instance;
        $this->id = null;
    }

    private function loadModel()
    {
        return Moa::instance()->finderFor($this->type)->findOne(array(
            '_id' => $this->id
        ));
    }
}