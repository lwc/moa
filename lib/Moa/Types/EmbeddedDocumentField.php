<?php

namespace Moa\Types;

use \Moa;

class EmbeddedDocumentField extends Type
{
    protected $defaultOptions = array(
        'required' => false,
        'type' => null
    );

    public static function construct($type=null)
    {
        return new EmbeddedDocumentField(array('type' => $type));
    }

    public function validate($value)
    {
        parent::validate($value);
        $type = $this->options['type'];
        if (isset($value) && !$value instanceof $type) {
            $this->error('is not an instance of '.$type);
        }

        if (isset($value)) {
            try {
                $value->validate();
            } catch (Moa\DomainObject\ValidationException $e) {
                $this->error('failed validation with message "'.$e->getMessage().'"');
            }
        }
    }

    public function toMongo(&$doc, &$mongoDoc, $key)
    {
        if (isset($doc[$key])) {
            $mongoDoc[$key] = $doc[$key]->toMongo();
        }
    }

    public function fromMongo(&$doc, &$mongoDoc, $key)
    {
        if (isset($mongoDoc[$key])) {
            $type = $this->options['type'];
            $model = new $type();
            $doc[$key] = $model->fromMongo($mongoDoc[$key]);
        }
    }
}
