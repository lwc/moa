<?php


require_once(__DIR__.'/base.php');

class Site extends Moa\DomainObject
{
}

class Comment extends Moa\Document
{
    public function properties()
    {
        return array(
            'body' => new Moa\Types\StringField(array('required' => true)),
            'rating' => new Moa\Types\FloatField(),
        );
    }
}

class BlogPost extends Moa\DomainObject
{
    public static function indexes()
    {
        return array(
            'title' => array('keys' => 'title')
        );
    }

    public function properties()
    {
        return array(
            'title' => new Moa\Types\StringField(array('required' => true)),
            'views' => new Moa\Types\IntegerField(),
            'comments' => new Moa\Types\ArrayField(array('type' => new Moa\Types\EmbeddedDocumentField(array('type' => 'Comment')))),
            'site' => new Moa\Types\ReferenceField(array('type' => 'Site'))
        );
    }
}


class DomainObjectTest extends MoaTest
{
    public function setUp()
    {
        \Mockery::getConfiguration()->setInternalClassMethodParamMap(
            'MongoCollection',
            'save',
            array('&$data', '$options = array()')
        );

        $this->collection = \Mockery::mock('MongoCollection');
        $this->collection->shouldReceive('ensureIndex');

        $this->db = \Mockery::mock('MongoDB');
        $this->db->shouldReceive('selectCollection')->andReturn($this->collection);
        Moa::setup($this->db);
    }

    private function expectDocumentSaved($document)
    {
        $this->collection
            ->shouldReceive('save')
            ->once()
            ->with($document, array('safe' => true))
            ->andReturnUsing(function(&$mongoDoc) {
                $mongoDoc['_id'] = 100;
            });
    }

    private function injectDocument($query, $document)
    {
        $cursor = Mockery::mock('MongoCursor');
        $cursor
            ->shouldReceive('limit')
            ->andReturn($cursor);
        $cursor
            ->shouldReceive('hasNext')
            ->andReturn(true, false, true, false);
        $cursor
            ->shouldReceive('getNext')
            ->andReturn($document);

        $this->collection
            ->shouldReceive('find')
            ->with($query, array())
            ->andReturn($cursor);
    }

    public function testFinderDelegates()
    {
        $this->injectDocument(
            array('title'=>'Test'),
            array('title' => 'Test', 'views' => 100)
        );

        $post = BlogPost::findOne(array('title' => 'Test'));

        $this->assertInstanceOf('BlogPost', $post);
        $this->assertEquals('Test', $post->title);
        $this->assertEquals(100, $post->views);
    }

    public function testSaveBasic()
    {
        $this->expectDocumentSaved(array(
                'title' => 'Hello World',
                'views' => 0,
                'comments' => array()
        ));

        $post = new BlogPost(array(
            'title' => 'Hello World',
            'views' => 0
        ));

        $this->assertNull($post->id());
        $post->save();
        $this->assertEquals(100, $post->id());
    }

    public function testRelations()
    {
        // post
        $this->injectDocument(
            array('title' => 'Test'),
            array('title' => 'Test', 'site' =>array('id' => 123, 'type' => 'Site'))
        );
        // site
        $this->injectDocument(
            array('_id' => 123),
            array('name' => 'Site 1', '_id' => 123)
        );

        $site1 = Site::findOne(array('_id' => 123));

        $post = BlogPost::findOne(array('title' => 'Test'));

        $postSite = $post->site;
        $this->assertEquals($site1->id(), $postSite->id());

    }
}