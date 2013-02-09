<?php

namespace Moa\Types;

class StringField extends Type
{
    public function validate($value)
    {
        parent::validate($value);
        if (isset($value) && !$this->isStringable($value)) {
            $this->error('is not stringable');
        }
    }

    public function toMongo(&$doc, &$mongoDoc, $key)
    {
        if (isset($doc[$key]))
            $mongoDoc[$key] = (string)$doc[$key];
    }

    /**
     * Scalar values and any object with a __toString method can be cast to a string
     * @param mixed $value
     * @return boolean
     */
    private function isStringable($value)
    {
        return (is_scalar($value) || (is_object($value) && is_callable(array($value, '__toString'))));
    }
}
