<?php

namespace Moa\DomainObject;
use \Moa;

abstract class LazyProperty
{
	private
		$identity,
		$instance
		;

	abstract protected function equals($instance, $identity);

	abstract protected function createIdentity($instance);

	abstract protected function loadInstance($identity);

	/**
	 * Return the identity.
	 *
	 * If we have an instance, make sure its saved and use its identity
	 */
	public function getIdentity()
	{
		if (!isset($this->identity) && isset($this->instance))
		{
			$this->identity = $this->createIdentity($this->instance);
		}

		return $this->identity;
	}

	/**
	 * Set the raw identity.
	 *
	 * If we have an instance and it's identity differs, remove it so that it
	 * may be lazily loaded on demand
	 */
	public function setIdentity($identity)
	{
		$this->identity = $identity;
		if ($this->instance && !$this->equals($this->instance, $identity))
			$this->instance = null;
	}

	/**
	 * If we have an identity or an instance, we have a value
	 */
	public function hasValue()
	{
		return (isset($this->identity) || isset($this->instance));
	}

	/**
	 * Property getter. Local instance takes precedence over identity
	 *
	 * Loads the instance from the identity if required
	 */
	public function get()
	{
		if (isset($this->instance))
			return $this->instance;

		if ($this->identity == null)
			return null;

		return $this->instance = $this->loadInstance($this->identity);
	}

	/**
	 * Property setter
	 *
	 * Unsets the local identity so that it may be lazily evaluated on save
	 */
	public function set($instance)
	{
		$this->instance = $instance;
		$this->identity = null;
	}

	/**
	 * For __unset behaviour
	 */
	public function del()
	{
		$this->instance = null;
		$this->identity = null;
	}
}