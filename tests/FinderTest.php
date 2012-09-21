<?php

require_once(__DIR__.'/base.php');


class FinderTest extends MoaTest
{
    public function testFind()
    {
        $cursor = Mockery::mock('MongoCursor');
        $collection = Mockery::mock('MongoCollection')
            ->shouldReceive('find')
            ->with(array('name'=>'Luke'), array())
            ->andReturn($cursor)
            ->mock();

        $wrappedCollection = new Moa\DomainObject\Finder($collection, 'MyModel');
        $res = $wrappedCollection->find(array('name'=>'Luke'));

        $this->assertEquals(get_class($res), 'Moa\DomainObject\Cursor');
    }

    public function testFindOne()
    {
        $cursor = Mockery::mock('MongoCursor');
        $collection = Mockery::mock('MongoCollection')
            ->shouldReceive('findOne')
            ->with(array('name'=>'Luke'), array())
            ->andReturn(array('name'=>'Luke', 'awesome'=>true))
            ->mock();

        $wrappedCursor = new Moa\DomainObject\Finder($collection, 'MyModel');
        $res = $wrappedCursor->findOne(array('name'=>'Luke'));
        
        $this->assertEquals(get_class($res), 'MyModel');
        $this->assertEquals($res->awesome, true);
    }

    public function testDecorationPropagates()
    {
        $collection = Mockery::mock('MongoCollection');
        $collection->shouldReceive('doMoreStuff')->andReturn($collection);

        $wrappedCollection = new Moa\DomainObject\Finder($collection, 'MyModel');
        $res = $wrappedCollection->doMoreStuff();

        $this->assertEquals(get_class($res), 'Moa\DomainObject\Finder');
    }
}