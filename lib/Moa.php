<?php

class Moa
{
    private static $instance;

    private $dbMap = array();

    private $finders = array();

    public function __construct($mongoDb)
    {
        $this->dbMap['default'] = $mongoDb;
    }

    public function addDatabase($name, $mongoDb)
    {
        $this->dbMap[$name] = $mongoDb;
    }

    public function finderFor($className)
    {
        if (!isset($this->finders[$className]))
            $this->finders[$className] = $this->createFinder($className);
        return $this->finders[$className];
    }

    public function createFinder($className)
    {
        $dbName = $className::getDatabaseName();
        $this->lazyConnect($dbName);

        $db = $this->dbMap[$dbName];
        $collection = $db->selectCollection($className::getCollectionName());
        $this->ensureIndexes($className, $collection, true);
        return new Moa\DomainObject\Finder($collection, $className);
    }

    public function ensureIndexes($className, $collection, $background)
    {
        foreach ($className::indexes() as $name => $index)
        {
            $keys = $index['keys'];
            $options = array();
            if (isset($index['options'])) {
                $options = $index['options'];
            }
            $options['background'] = $background;
            $options['safe'] = !$background;
            $options['name'] = $name;
            $collection->ensureIndex($keys, $options);
        }
    }

    private function lazyConnect($dbName)
    {
        if (!isset($this->dbMap[$dbName])) {
            throw new Moa\Exception('No database registered for "'.$dbName.'"');
        }

        $factory = $this->dbMap[$dbName];

        if (is_callable($factory)) {
            $this->dbMap[$dbName] = $factory();
        }

        if (!$this->dbMap[$dbName] instanceof \MongoDB) {
            throw new Moa\Exception('Invalid MongoDB instance registered for "'.$dbName.'"');
        }
    }

    public static function setup($mongoDb)
    {
        return self::reset(new self($mongoDb));
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