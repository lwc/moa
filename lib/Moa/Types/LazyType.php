<?php

namespace Moa\Types;

abstract class LazyType extends Type
{
    abstract public function get(&$doc, $key);

    abstract public function set(&$doc, $key, $value);

    abstract public function del(&$doc, $key);

    abstract public function hasValue(&$doc, $key);

    public function isLazy()
    {
        return true;
    }
}