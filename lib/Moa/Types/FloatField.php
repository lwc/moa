<?php

namespace Moa\Types;

class FloatField extends Type
{
    public function validate($value)
    {
        parent::validate($value);
        if (isset($value) && (!is_float($value) && !is_integer($value)))
            $this->error('is not a float');
    }

    public function toMongo(&$doc, &$mongoDoc, $key)
    {
        if (array_key_exists($key, $doc) && is_integer($doc[$key]))
            $mongoDoc[$key] = (float)$doc[$key];
        else
            parent::toMongo($doc, $mongoDoc, $key);
    }    
}
