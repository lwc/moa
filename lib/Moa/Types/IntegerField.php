<?php

namespace Moa\Types;

class IntegerField extends Type
{
    public function validate($value)
    {
        parent::validate($value);
        if (isset($value) && !is_integer($value))
            $this->error('is not a integer');
    }
}
