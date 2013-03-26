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
        $cursor
            ->shouldReceive('limit')
            ->andReturn($cursor);
        $cursor
            ->shouldReceive('hasNext')
            ->andReturn(true, false);
        $cursor
            ->shouldReceive('getNext')
            ->andReturn(array('name'=>'Luke', 'awesome'=>true));

        $collection = Mockery::mock('MongoCollection')
            ->shouldReceive('find')
            ->with(array('name'=>'Luke'), array())
            ->andReturn($cursor)
            ->mock();

        $wrappedCursor = new Moa\DomainObject\Finder($collection, 'MyModel');
        $res = $wrappedCursor->findOne(array('name'=>'Luke'));

        $this->assertEquals(get_class($res), 'MyModel');
        $this->assertEquals($res->awesome, true);
    }

    public function testFindOneZeroReturned()
    {
        $cursor = Mockery::mock('MongoCursor');
        $cursor
            ->shouldReceive('limit')
            ->andReturn($cursor);
        $cursor
            ->shouldReceive('hasNext')
            ->andReturn(false, false);
        $cursor
            ->shouldReceive('getNext')
            ->andReturn(array('name'=>'Luke', 'awesome'=>true));

        $collection = Mockery::mock('MongoCollection')
            ->shouldReceive('find')
            ->with(array('name'=>'Luke'), array())
            ->andReturn($cursor)
            ->mock();

        $wrappedCursor = new Moa\DomainObject\Finder($collection, 'MyModel');

        try {
            $res = $wrappedCursor->findOne(array('name'=>'Luke'));
            $this->fail('Failed to catch expected NoMatchingDocumentsException');
        }
        catch (Moa\NoMatchingDocumentsException $e) {
        }
    }

    public function testFindOneMultipleReturned()
    {
        $cursor = Mockery::mock('MongoCursor');
        $cursor
            ->shouldReceive('limit')
            ->andReturn($cursor);
        $cursor
            ->shouldReceive('hasNext')
            ->andReturn(true, true);
        $cursor
            ->shouldReceive('getNext')
            ->andReturn(array('name'=>'Luke', 'awesome'=>true));

        $collection = Mockery::mock('MongoCollection')
            ->shouldReceive('find')
            ->with(array('name'=>'Luke'), array())
            ->andReturn($cursor)
            ->mock();

        $wrappedCursor = new Moa\DomainObject\Finder($collection, 'MyModel');

        try {
            $res = $wrappedCursor->findOne(array('name'=>'Luke'));
            $this->fail('Failed to catch expected MultipleMatchingDocumentsException');
        }
        catch (Moa\MultipleMatchingDocumentsException $e) {
        }
    }

    public function testDecorationPropagates()
    {
        $collection = Mockery::mock('MongoCollection');
        $collection->shouldReceive('doMoreStuff')->andReturn($collection);

        $wrappedCollection = new Moa\DomainObject\Finder($collection, 'MyModel');
        $res = $wrappedCollection->doMoreStuff();

        $this->assertEquals(get_class($res), 'Moa\DomainObject\Finder');
    }

    public function testSave()
    {
        \Mockery::getConfiguration()->setInternalClassMethodParamMap(
            'MongoCollection',
            'save',
            array('&$data', '$options = array()')
        );

        $collection = Mockery::mock('MongoCollection')
            ->shouldReceive('save')
            ->with(array('key'=>'value'), array())
            ->andReturnUsing(function(&$mongoDoc) {
                $mongoDoc['_id'] = 100;
            })
            ->mock();

        $wrappedCollection = new Moa\DomainObject\Finder($collection, 'MyModel');

        $model = new MyModel(array(
            'key' => 'value'
        ));

        $wrappedCollection->save($model);

        $this->assertEquals(100, $model->id());

    }
}