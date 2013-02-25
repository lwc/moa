<?php

namespace Moa\DomainObject;

use \Moa;

class Finder
{
    private $className;
    private $collection;

    public function __construct($collection, $className)
    {
        $this->className = $className;
        $this->collection = $collection;
    }

    public function find($query=array(), $fields=array())
    {
        $cursor = $this->collection->find($query, $fields);
        return new Moa\DomainObject\Cursor($cursor, $this->className);
    }

    public function findOne($query, $fields=array())
    {
        $className = $this->className;
        $document = new $className();
        return $document->fromMongo($this->collection->findOne($query, $fields));
    }

    public function save($document, $options=array())
    {
        $mongoDoc = $document->toMongo();
        $res = $this->collection->save($mongoDoc, $options);
        $document->_id = $mongoDoc['_id'];
        return $res;
    }

    public function __call($func, $args)
    {
        $res = call_user_func_array(array($this->collection, $func), $args);

        if ($res instanceof \MongoCollection) {
            $res = new static($res, $this->className);
        }

        return $res;
    }
}

