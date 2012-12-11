<?php

namespace Moa\Types;

class FloatField extends Type
{
    public function validate($value)
    {
        parent::validate($value);
        if (isset($value) && !is_numeric($value))
            $this->error('is not numeric');
    }

    public function toMongo(&$doc, &$mongoDoc, $key)
    {
        if (isset($doc[$key]))
            $mongoDoc[$key] = (float)$doc[$key];
    }
}
