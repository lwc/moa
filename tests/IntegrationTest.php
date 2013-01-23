<?php


require_once(__DIR__.'/base.php');


class IntegrationTest // extends MoaTest
{
    public function testLazyConnectionNeverCalled()
    {
        $methodCalled = false;
        Moa::setup(function() use(&$methodCalled) {
            $methodCalled = true;
        });

        Moa::instance()->addDatabase('another', \Mockery::mock('MongoDB'));
        $this->assertFalse($methodCalled);
    }

    public function testLazyConnectionCalled()
    {
        $methodCalled = false;
        $db = \Mockery::mock('MongoDB');
        $db->shouldReceive('selectCollection')->once();


        Moa::setup(function() use(&$methodCalled, $db) {
            $methodCalled = true;
            return $db;
        });

        Moa::instance()->finderFor('MyModel');

        $this->assertTrue($methodCalled);
    }

    public function testConnectionNotDatabase()
    {
        Moa::setup((object)array());

        try
        {
            Moa::instance()->finderFor('MyModel');
            $this->fail('Expected invalid database exception');
        }
        catch (Moa\Exception $e)
        {
        }
    }

    public function testEnsureIndexes()
    {
        $db = \Mockery::mock('MongoDB');
        $coll = \Mockery::mock('MongoCollection');
        $db->shouldReceive('selectCollection')->once()->andReturn($coll);
        $coll->shouldReceive('ensureIndex')->once()->with('title', array(
            'background' => true,
            'safe' => false,
            'name' => 'title'
        ));
        $db->shouldReceive('selectCollection')->once()->andReturn($coll);

        Moa::setup($db);

        Moa::instance()->finderFor('BlogPost');
    }
}