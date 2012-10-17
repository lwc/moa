<?php

namespace Moa\DomainObject;
use \Moa;

class ReferenceProperty extends Moa\DomainObject\LazyProperty
{
    protected function equals($instance, $identity)
    {
        if ($identity['id'] != $instance->id())
            return false;
        if ($identity['type'] != get_class($instance))
            return false;
        return true;
    }

    protected function createIdentity($instance)
    {
        if (!$instance->saved())
            $instance->save();

        return array(
            'id' => $instance->id(),
            'type' => get_class($instance)
        );
    }

    protected function loadInstance($identity)
    {
        return Moa::instance()->finderFor($identity['type'])->findOne(array(
            '_id' => $identity['id']
        ));
    }
}