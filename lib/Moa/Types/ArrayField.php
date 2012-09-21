<?php

namespace Moa\Types;

class ArrayField extends Type
{
    protected $defaultOptions = array(
        'required' => false,
        'type' => null
    );

    public function validate($value)
    {
        parent::validate($value);
        if (isset($value) && !is_array($value))
            $this->error('is not an array');

        $type = $this->options['type'];
        if (isset($value) && $type)
        {
            $result = array_reduce($value, function($result, $item) use ($type) {

                try
                {
                    $type->validate($item);
                    return $result && true;
                }
                catch(TypeException $e)
                {
                    return false;
                }
            }, true);

            if (!$result)
                $this->error('contains types other than '.get_class($type));
        }
    }

    public function toMongo(&$doc, &$mongoDoc, $key)
    {
        $type = $this->options['type'];
        if ($type && isset($doc[$key]))
        {
            $mongoDoc[$key] = array();
            foreach ($doc[$key] as $k => $v)
            {
                $type->toMongo($doc[$key], $mongoDoc[$key], $k);
            }
        }
        else
            parent::toMongo($doc, $mongoDoc, $key);
    }

    public function fromMongo(&$doc, &$mongoDoc, $key)
    {
        $type = $this->options['type'];
        if ($type && isset($mongoDoc[$key]))
        {
            $doc[$key] = array();
            foreach ($mongoDoc[$key] as $k => $v)
            {
                $type->fromMongo($doc[$key], $mongoDoc[$key], $k);
            }
        }
        else
            parent::fromMongo($doc, $mongoDoc, $key);
    }    
}
