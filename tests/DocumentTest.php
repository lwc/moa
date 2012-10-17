<?php

require_once(__DIR__.'/base.php');


class TestDocument extends Moa\Document
{
    public function properties()
    {
        return array(
            'myInt' => new Moa\Types\IntegerField(array('required' => true)),            
            'myString' => new Moa\Types\StringField(),
            'myArray' => new Moa\Types\ArrayField(),
            'myOwnSelf' => new Moa\Types\EmbeddedDocumentField(array('type'=>'TestDocument')),
            'myRef' => new Moa\Types\ReferenceField(array('type'=>'TestModel')),
        );
    }
}

class TestModel extends Moa\DomainObject
{
    public function properties()
    {
        return array(
            'number' => new Moa\Types\IntegerField(array('required' => true))
        );
    }
}

class DocumentTest extends MoaTest
{
    public function testValidateFailMissingRequired()
    {
        $doc = new TestDocument(array(
            'myString' => 'hello'
        ));
        $this->expectValidationFailure($doc);

        $doc->myInt = 234;
        $this->expectValidationSuccess($doc);
    }

    public function testValidateFailWrongType()
    {
        $doc = new TestDocument(array(
            'myInt' => 4345
        ));
        $this->expectValidationSuccess($doc);

        $doc->myString = true;
        $this->expectValidationFailure($doc);   
    }

    public function testEmbeddedFailure()
    {
        $doc = new TestDocument(array(
            'myInt' => 4345
        ));
        $this->expectValidationSuccess($doc);

        $doc->myOwnSelf = new TestDocument();
        $this->expectValidationFailure($doc);

        $doc->myOwnSelf->myInt = 234;
        $this->expectValidationSuccess($doc);
    }

    public function testRelatedFailure()
    {
        $doc = new TestDocument(array(
            'myInt' => 4345
        ));
        $this->expectValidationSuccess($doc);

        $doc->myRef = 3456;
        $this->expectValidationFailure($doc);

        $doc->myRef = new MyModel();
        $this->expectValidationFailure($doc);

        $doc->myRef = new TestModel();
        $this->expectValidationFailure($doc);

        $doc->myRef->number = 234;
        $this->expectValidationSuccess($doc);        
    }

    public function testFromMongo()
    {
        $mongoDoc = array(
            'myInt' => 123,
            'myArray' => array(1,2,3,4,5),
            'myOwnSelf' => array('myString' => 'world'),
            'myExtraField' => 'is allowed'
        );
        $doc = new TestDocument();
        $doc->fromMongo($mongoDoc);

        $this->assertEquals($doc->myInt, 123);
        $this->assertEquals($doc->myOwnSelf->myString, 'world');
        $this->assertEquals($doc->myArray[0], 1);
        $this->assertEquals($doc->myExtraField, 'is allowed');
        $this->expectValidationFailure($doc);

        $doc->myOwnSelf->myInt = 100;
        $this->expectValidationSuccess($doc);
    }

    public function testToMongo()
    {
        $doc = new TestDocument(array(
            'myInt' => 100,
            'myArray' => array(
                'key' => 'value',
            ),
            'myOwnSelf' => new TestDocument(array(
                'myOtherKey' => true
            ))
        ));

        $mongoDoc = $doc->toMongo();

        $this->assertEquals($mongoDoc['myInt'], 100);
        $this->assertEquals($mongoDoc['myArray']['key'], 'value');
        $this->assertTrue(is_array($mongoDoc['myOwnSelf']));
        $this->assertEquals($mongoDoc['myOwnSelf']['myOtherKey'], true);
    }

    public function testIsset()
    {
        $doc = new TestDocument(array(
            'myInt' => 100,
            'myArray' => array(
                'key' => 'value',
            ),
            'myOwnSelf' => new TestDocument(array(
                'myOtherKey' => true
            ))
        ));

        $this->assertTrue(isset($doc->myInt));
        $this->assertFalse(isset($doc->nothingHere));
        $this->assertTrue(isset($doc->myOwnSelf));
        $this->assertTrue(isset($doc->myOwnSelf->myOtherKey));
        $this->assertFalse(isset($doc->myOwnSelf->myInt));
        $this->assertFalse(isset($doc->myRef));
    }

    public function testUnset()
    {
         $doc = new TestDocument(array(
            'myInt' => 100,
            'myArray' => array(
                'key' => 'value',
            ),
            'myOwnSelf' => new TestDocument(array(
                'myOtherKey' => true
            ))
        ));

        $this->assertTrue(isset($doc->myInt));
        unset($doc->myInt);
        $this->assertFalse(isset($doc->myInt));

        $this->assertTrue(isset($doc->myOwnSelf));
        $this->assertTrue(isset($doc->myOwnSelf->myOtherKey));

        unset($doc->myOwnSelf->myOtherKey);
        $this->assertFalse(isset($doc->myOwnSelf->myOtherKey));

        unset($doc->myOwnSelf);
        $this->assertFalse(isset($doc->myOwnSelf));

        // Lazy property example
        $model = new MyModel();
        $this->assertFalse(isset($doc->myRef));
        $doc->myRef = $model;
        $this->assertTrue(isset($doc->myRef));
        $this->assertSame($model, $doc->myRef);

        unset($doc->myRef);
        $this->assertFalse(isset($doc->myRef));

    }

    private function expectValidationFailure($document)
    {
        try
        {
            $document->validate();       
            $this->fail('Expected validation to fail');
        }
        catch (Moa\DomainObject\ValidationException $e)
        {

        }
    }

    private function expectValidationSuccess($document)
    {
        try
        {
            $document->validate();       
        }
        catch (Moa\DomainObject\ValidationException $e)
        {
            $this->fail('Expected validation to succeed, failed with message: '.$e->getMessage());
        }
    }    
}