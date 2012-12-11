<?php

namespace Moa\DomainObject;
use \Moa;

class TypedArrayIterator extends \ArrayIterator
{
	public function current()
	{
		return parent::current()->get();
	}
}


class TypedArrayObject extends \ArrayObject
{
	/**
	 *
	 * @var Moa\Types\LazyType
	 */
	private $type;

	public function __construct($array, $type, $fromRaw)
	{
		$this->type = $type;

		if (isset($type))
		{
			$mongoDoc = $array;
			foreach ($array as $k => $v)
			{
				if (!$fromRaw)
					$this->type->set($array, $k, $v);
			}
		}

		parent::__construct($array);
	}

	public function getIterator()
	{
		return new TypedArrayIterator($this);
	}

	public function append($value)
	{
		$doc = array();
		$this->type->set($doc, 0, $value);
		return parent::append($doc[0]);
	}

	public function offsetGet($index)
	{
		$array = $this->getArrayCopy();
		return parent::offsetGet($index)->get($array, $index);
	}

	public function offsetSet($index, $newval)
	{
		$array = $this->getArrayCopy();
		$this->type->set($array, $index, $newval);
		return parent::offsetSet($index, $array[$index]);
	}

	public function offsetExists($index)
	{
		return parent::offsetExists($index);
	}

	public function offsetUnset($index)
	{
		return parent::offsetUnset($index);
	}
}