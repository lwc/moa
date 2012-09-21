<?php

require_once(__DIR__.'/base.php');


class CursorTest extends MoaTest
{
    public function testDecoration()
    {
        $cursor = Mockery::mock('MongoCursor')->shouldReceive('doStuff')->andReturn(true)->mock();

        $wrappedCursor = new Moa\DomainObject\Cursor($cursor, 'MyModel');
        $res = $wrappedCursor->doStuff();

        $this->assertTrue($res);
    }

    public function testDecorationPropagates()
    {
        $cursor = Mockery::mock('MongoCursor');
        $cursor->shouldReceive('doMoreStuff')->andReturn($cursor);

        $wrappedCursor = new Moa\DomainObject\Cursor($cursor, 'MyModel');
        $res = $wrappedCursor->doMoreStuff();

        $this->assertEquals(get_class($res), 'Moa\DomainObject\Cursor');
    }

    public function testGetNext()
    {
        $cursor = Mockery::mock('MongoCursor');
        $cursor->shouldReceive('getNext')->andReturn(array('name' => 'Luke'));

        $wrappedCursor = new Moa\DomainObject\Cursor($cursor, 'MyModel');
        
        $model = $wrappedCursor->getNext();

        $this->assertEquals(get_class($model), 'MyModel');
        $this->assertEquals($model->name, 'Luke');
    }
}