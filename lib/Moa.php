<?php

class Moa
{
	private static $instance;

	private
		$conn,
		$dbMap = array(),
		$finders = array();

	public function __construct($conn, $defaultDbName)
	{
		$this->conn = $conn;
		$this->dbMap['default'] = $defaultDbName;
	}

	public function addDatabase($name, $alias=null)
	{
		$alias ?: $name;
		$this->dbMap[$alias] = $name;
	}

    public function finderFor($className)
    {
		if (!isset($this->finders[$className]))
            $this->finders[$className] = $this->createFinder($className);
        return $this->finders[$className];
    }

    public function createFinder($className)
    {
		$this->lazyConnect();
		$alias = $className::getDatabaseName();
		$db = $this->conn->selectDB($this->dbMap[$alias]);
        $collection = $db->selectCollection($className::getCollectionName());
        $this->ensureIndexes($className, $collection, true);
        return new Moa\DomainObject\Finder($collection, $className);
    }

    public function ensureIndexes($className, $collection, $background)
	{
		foreach ($className::indexes() as $name => $index)
		{
			$keys = $index['keys'];
			$options = $index['options'];
			$options['background'] = $background;
			$options['safe'] = !$background;
			$options['name'] = $name;
			$collection->ensureIndex($keys, $options);
		}
	}

	private function lazyConnect()
	{
		$connFactory = $this->conn;
		if (is_callable($connFactory))
		{
			$this->conn = $connFactory();
		}
	}

	public static function setup($conn, $defaultDbName)
	{
		return self::reset(new self($conn, $defaultDbName));
	}

	public static function instance()
	{
		return self::$instance;
	}

	public static function reset($instance)
	{
		return self::$instance = $instance;
	}
}