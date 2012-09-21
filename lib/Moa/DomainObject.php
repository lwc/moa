<?php

namespace Moa;
use \Moa;

abstract class DomainObject extends Document
{
    public function save()
    {
		$this->validate();
        return self::finder()->save($this);
    }

    public static function indexes()
    {
        return array();
    }

	public static function getCollectionName()
    {
        return strtolower(array_pop(explode('_', get_called_class())));
    }

	public static function getDatabaseName()
	{
		return 'default';
	}

    public static function finder()
    {
        return Moa::instance()->finderFor(get_called_class());
    }

    public static function __callStatic($func, $args)
    {
        $finder = static::finder();
        return call_user_func_array(array($finder, $func), $args);
    }
}