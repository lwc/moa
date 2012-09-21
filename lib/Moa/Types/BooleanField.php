<?php

namespace Moa\Types;

class BooleanField extends Type
{
    public function validate($value)
    {
        parent::validate($value);
        if (isset($value) && !is_bool($value))
            $this->error('is not boolean');
    }
}
