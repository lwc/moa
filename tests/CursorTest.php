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
        $this->assertSame($cursor, $wrappedCursor->getRawCursor());
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

    public function testIteration()
    {
        $cursor = Mockery::mock('MongoCursor');
        $cursor->shouldReceive('rewind')->once();
        $cursor->shouldReceive('valid')->twice()->andReturn(true, false);
        $cursor->shouldReceive('current')->once()->andReturn(array('key'=>'value'));
        $cursor->shouldReceive('next')->once();
        $cursor->shouldReceive('key')->once()->andReturn(0);

        $wrappedCursor = new Moa\DomainObject\Cursor($cursor, 'MyModel');
        
        foreach ($wrappedCursor as $i => $model)
        {
            $this->assertInstanceOf('MyModel', $model);
            $this->assertEquals('value', $model->key);
        }
    }
}