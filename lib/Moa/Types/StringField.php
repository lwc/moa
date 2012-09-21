<?php

namespace Moa\Types;

class StringField extends Type
{
    public function validate($value)
    {
        parent::validate($value);
        if (isset($value) && !is_string($value))
            $this->error('is not a string');
    }
}
