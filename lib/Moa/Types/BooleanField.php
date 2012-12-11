<?php

namespace Moa\Types;

class BooleanField extends Type
{
    public function validate($value)
    {
        parent::validate($value);
        if (isset($value) && !is_scalar($value))
            $this->error('is not scalar');
    }

    public function toMongo(&$doc, &$mongoDoc, $key)
    {
        if (isset($doc[$key]))
            $mongoDoc[$key] = (bool)$doc[$key];
    }
}
