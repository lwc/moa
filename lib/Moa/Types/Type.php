<?php

namespace Moa\Types;

class Type
{
    protected $defaultOptions = array(
        'required' => false,
    );

	/**
	 * @return Type
	 */
    public static function construct()
	{
		$className = get_called_class();
		return $className();
	}

	public function required($required)
	{
		$this->options['required'] = $required;
	}

	public function __construct($options = null)
    {
        if (!$options)
            $options = array();
        $this->options = array_merge($this->defaultOptions, $options);
    }

    public function isRequired()
    {
        return $this->options['required'];
    }

    public function isLazy()
    {
        return false;
    }

    public function validate($value)
    {
        if ($this->isRequired() && !isset($value))
            $this->error('is required');
    }

    public function toMongo(&$doc, &$mongoDoc, $key)
    {
        if (array_key_exists($key, $doc))
            $mongoDoc[$key] = $doc[$key];
    }

    public function fromMongo(&$doc, &$mongoDoc, $key)
    {
        if (array_key_exists($key, $mongoDoc))
            $doc[$key] = $mongoDoc[$key];
    }

    protected function error($reason)
    {
        throw new TypeException($reason);
    }
}