<?php

namespace Moa\Types;

class DateField extends Type
{
    protected $defaultOptions = array(
        'required' => false,
        'storeTimezone' => false
    );

	public function storeTimezone($storeTimezone)
	{
		$this->options['storeTimezone'] = $storeTimezone;
	}

    public function validate($value)
    {
        parent::validate($value);
        if (isset($value) && !($value instanceof \DateTime))
            $this->error('is not a DateTime instance');
    }

    public function toMongo(&$doc, &$mongoDoc, $key)
    {
        if (!array_key_exists($key, $doc))
            return;

        $mongoDoc[$key] = new \MongoDate($doc[$key]->format('U'));

        if ($this->options['storeTimezone'])
            $mongoDoc[$key.'__tz'] = $doc[$key]->getTimezone()->getName();

    }

    public function fromMongo(&$doc, &$mongoDoc, $key)
    {
        if (!array_key_exists($key, $mongoDoc))
            return;

        $date = new \DateTime('@'.$mongoDoc[$key]->sec);

        if ($this->options['storeTimezone'])
        {
            $tz = new \DateTimeZone($mongoDoc[$key.'__tz']);
            $date->setTimezone($tz);
        }

        $doc[$key] = $date;
    }
}
