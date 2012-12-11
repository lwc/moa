<?php

namespace Moa\DomainObject;
use \Moa;

class ArrayProperty
{
	private
		$arrayObject,
		$type;


	public function __construct($type)
	{
		$this->type = $type;
	}

	/**
	 * Return the identity.
	 *
	 * If we have an instance, make sure its saved and use its identity
	 */
	public function getIdentity()
	{
		if (isset($this->arrayObject))
		{
			$array = $this->arrayObject->getArrayCopy();
			if (isset($this->type))
			{
				$doc = $array;
				foreach ($array as $k => $v)
				{
					$this->type->toMongo($doc, $array, $k);
				}
			}
			return $array;
		}
	}

	/**
	 * Set the raw identity.
	 *
	 * If we have an instance and it's identity differs, remove it so that it
	 * may be lazily loaded on demand
	 */
	public function setIdentity($array)
	{
        if ($this->type && is_array($array))
        {
			$mongoDoc = $array;
            foreach ($array as $k => $v)
            {
                $this->type->fromMongo($array, $mongoDoc, $k);
            }
        }

		if (is_array($array))
			$this->arrayObject = $this->createArrayObject($array, true);
	}

	/**
	 * If we have an identity or an instance, we have a value
	 */
	public function hasValue()
	{
		return isset($this->arrayObject);
	}

	/**
	 * Property getter. Local instance takes precedence over identity
	 *
	 * Loads the instance from the identity if required
	 */
	public function get()
	{
		return $this->arrayObject;
	}

	/**
	 * Property setter
	 *
	 * Unsets the local identity so that it may be lazily evaluated on save
	 */
	public function set($array)
	{
		$this->arrayObject = $this->createArrayObject($array, false);
	}

	/**
	 * For __unset behaviour
	 */
	public function del()
	{
		unset($this->arrayObject);
	}

	private function createArrayObject($array, $fromRaw)
	{
		if (isset($this->type) && $this->type->isLazy())
			return new TypedArrayObject($array, $this->type, $fromRaw);
		return new \ArrayObject($array);
	}
}