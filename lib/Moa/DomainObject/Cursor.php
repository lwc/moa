<?php

namespace Moa\DomainObject;
use \Moa;

class Cursor implements \Iterator
{
    private
        $cursor,
        $className;

    public function __construct($cursor, $className)
    {
        $this->cursor = $cursor;
        $this->className = $className;
    }

    public function getRawCursor()
    {
        return $this->cursor;
    }

    public function getNext()
    {
        return $this->createModel($this->cursor->getNext());
    }

    public function createModel($document)
    {
        $className = $this->className;
        $model = new $className();
        return $model->fromMongo($document);
    }

    public function current()
    {
        return $this->createModel($this->cursor->current());
    }

    public function next()
    {
        return $this->cursor->next();
    }

    public function valid()
    {
        return $this->cursor->valid();
    }
    public function key()
    {
        return $this->cursor->key();
    }
    public function rewind()
    {
        return $this->cursor->rewind();
    }

    public function __call($func, $args)
    {
        $res = call_user_func_array(array($this->cursor, $func), $args);

        if ($res instanceof \MongoCursor)
            $res = new static($res, $this->className);
        return $res;
    }
}