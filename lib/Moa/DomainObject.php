<?php

namespace Moa;
use \Moa;

abstract class DomainObject extends Document
{
    protected function beforeSave()
    {
    }

    protected function afterSave()
    {
    }

    protected function beforeLoad()
    {
    }

    protected function afterLoad()
    {
    }

    public function save()
    {
        $this->beforeSave();
		$this->validate();
        $result = self::finder()->save($this, array('safe'=> true));
        $this->afterSave();
        return $result;
    }

    public function fromMongo($mongoDoc)
    {
        $this->beforeLoad();
        $result = parent::fromMongo($mongoDoc);
        $this->afterLoad();
        return $result;
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