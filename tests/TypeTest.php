<?php

require_once(__DIR__.'/base.php');


class TypeTest extends MoaTest
{
    public function testBooleanField()
    {
        $type = new Moa\Types\BooleanField();
        $this->expectValidationSuccess($type, 123);
        $this->expectValidationSuccess($type, 'dgfdf');
        $this->expectValidationFailure($type, array());
        $this->expectValidationSuccess($type, 123.123);     
        $this->expectValidationSuccess($type, null);
        $this->expectValidationSuccess($type, false);

        $type = new Moa\Types\BooleanField(array('required' => true));
        $this->expectValidationFailure($type, null);
        $this->expectValidationSuccess($type, true);
    }

    public function testStringField()
    {
        $type = new Moa\Types\StringField();
        $this->expectValidationSuccess($type, 1);
        $this->expectValidationSuccess($type, true);
        $this->expectValidationFailure($type, array());
        $this->expectValidationSuccess($type, null);
        $this->expectValidationSuccess($type, 'hello');

        $type = new Moa\Types\StringField(array('required' => true));
        $this->expectValidationFailure($type, null);
        $this->expectValidationSuccess($type, 'hello');
    }

    public function testIntegerField()
    {
        $type = new Moa\Types\IntegerField();
        $this->expectValidationFailure($type, 'hello');
        $this->expectValidationFailure($type, true);
        $this->expectValidationFailure($type, array());
        $this->expectValidationSuccess($type, 123.123);     
        $this->expectValidationSuccess($type, null);
        $this->expectValidationSuccess($type, 123);

        $type = new Moa\Types\IntegerField(array('required' => true));
        $this->expectValidationFailure($type, null);
        $this->expectValidationSuccess($type, 154);
    }

    public function testFloatField()
    {
        $type = new Moa\Types\FloatField();
        $this->expectValidationFailure($type, 'hello');
        $this->expectValidationFailure($type, true);
        $this->expectValidationFailure($type, array());
        $this->expectValidationSuccess($type, 123);
        $this->expectValidationSuccess($type, null);
        $this->expectValidationSuccess($type, 123.234);

        $type = new Moa\Types\FloatField(array('required' => true));
        $this->expectValidationFailure($type, null);
        $this->expectValidationSuccess($type, 154.234);
        $this->expectValidationSuccess($type, 123);

        $doc = array('someNum' => 123);
        $mongoDoc = array();
        $type->toMongo($doc, $mongoDoc, 'someNum');
        $this->assertTrue(is_float($mongoDoc['someNum']));

        $doc = array('someNum' => 123.00);
        $mongoDoc = array();
        $type->toMongo($doc, $mongoDoc, 'someNum');
        $this->assertTrue(is_float($mongoDoc['someNum']));        
    }

    public function testTypelessArrayField()
    {
        $type = new Moa\Types\ArrayField();
        $this->expectValidationFailure($type, 'hello');
        $this->expectValidationFailure($type, true);
        $this->expectValidationFailure($type, 123.234);
        $this->expectValidationFailure($type, 123);
        $this->expectValidationSuccess($type, null);
        $this->expectValidationSuccess($type, array(1,2,3));

        $type = new Moa\Types\ArrayField(array('required' => true));
        $this->expectValidationFailure($type, null);
        $this->expectValidationSuccess($type, array());

    }

    public function testTypedArrayField()
    {
        $type = new Moa\Types\ArrayField(array('type'=> new Moa\Types\IntegerField()));
        $this->expectValidationFailure($type, 'hello');
        $this->expectValidationFailure($type, true);
        $this->expectValidationFailure($type, 123.234);
        $this->expectValidationFailure($type, 123);
        $this->expectValidationSuccess($type, array(1,'2',3));      
        $this->expectValidationSuccess($type, null);
        $this->expectValidationSuccess($type, array());
        $this->expectValidationSuccess($type, array(1,2,3));

        $type = new Moa\Types\ArrayField(array(
            'required' => true,
            'type'=> new Moa\Types\IntegerField()
        ));
        $this->expectValidationFailure($type, null);
        $this->expectValidationSuccess($type, array());
        $this->expectValidationSuccess($type, array(1,2,3));
    }

    public function testTypedArrayFieldPropagatesToMongo()
    {
        $type = new Moa\Types\ArrayField(array(
            'required' => true,
            'type'=> new Moa\Types\FloatField()
        ));
        $this->expectValidationSuccess($type, array(1,2,3));
        $doc = array();
        $type->set($doc, 'someNum', array(1,2,3));

        $mongoDoc = array();
        $type->toMongo($doc, $mongoDoc, 'someNum');
        $this->assertTrue(is_float($mongoDoc['someNum'][0]));       
    }


    public function testTypedArrayFieldPropagatesFromMongo()
    {
        $type = new Moa\Types\ArrayField(array(
            'required' => true,
            'type'=> new Moa\Types\EmbeddedDocumentField(array('type'=>'MyModel'))
        ));

        $doc = array();
        $mongoDoc = array('someThings' => array(array('key'=>'value')));
        $type->fromMongo($doc, $mongoDoc, 'someThings');

        $arr = $doc['someThings']->get();
        $this->assertInstanceOf('MyModel', $arr[0]);
        $this->assertEquals('value', $arr[0]->key);
    }

    public function testDateField()
    {
        $tz = new DateTimeZone('Australia/Melbourne');
        $d = new DateTime('2010-01-01', $tz);

        $type = new Moa\Types\DateField();
        $this->expectValidationFailure($type, 123); 
        $this->expectValidationSuccess($type, null);
        $this->expectValidationSuccess($type, $d);

        $type = new Moa\Types\DateField(array('required' => true));
        $this->expectValidationFailure($type, null);
        $this->expectValidationSuccess($type, $d);

        $doc = array('someDate' => $d);
        $mongoDoc = array();
        $type->toMongo($doc, $mongoDoc, 'someDate');
        $this->assertTrue($mongoDoc['someDate'] instanceof MongoDate);
        $this->assertEquals($d->getTimestamp(), $mongoDoc['someDate']->sec);
    }   

    public function testDateFieldWithTimezone()
    {
        $tz = new DateTimeZone('Australia/Melbourne');
        $d = new DateTime('2010-01-01', $tz);

        $type = new Moa\Types\DateField(array('storeTimezone' => true));
        $this->expectValidationSuccess($type, null);

        $doc = array('someDate' => $d);
        $mongoDoc = array();
        $type->toMongo($doc, $mongoDoc, 'someDate');
        $this->assertTrue($mongoDoc['someDate'] instanceof MongoDate);
        $this->assertEquals($d->getTimestamp(), $mongoDoc['someDate']->sec);
        $this->assertEquals('Australia/Melbourne', $mongoDoc['someDate__tz']);

        $doc = array();
        $mongoDoc = array('someDate' => new MongoDate(strtotime('2010-01-01')), 'someDate__tz' => 'Australia/Sydney');
        $type->fromMongo($doc, $mongoDoc, 'someDate');
        $this->assertTrue($doc['someDate'] instanceof DateTime);
        $this->assertEquals('Australia/Sydney', $doc['someDate']->getTimeZone()->getName());
    }

    public function testEmbeddedDocumentField()
    {
        $type = new Moa\Types\EmbeddedDocumentField(array('type'=>'MyDocument'));
        $this->expectValidationFailure($type, 123);
        $this->expectValidationFailure($type, 'dgfdf');
        $this->expectValidationFailure($type, array());
        $this->expectValidationFailure($type, true);
        $this->expectValidationFailure($type, new MyModel());
        $this->expectValidationSuccess($type, null);
        $this->expectValidationSuccess($type, new MyDocument());

        $type = new Moa\Types\EmbeddedDocumentField(array('type'=>'MyDocument', 'required' => true));
        $this->expectValidationFailure($type, null);
        $this->expectValidationSuccess($type, new MyDocument());
    }

    public function testEmbeddedDocumentFieldToMongo()
    {
        $type = new Moa\Types\EmbeddedDocumentField(array('type'=>'MyDocument'));
        $doc = array('someDoc' => new MyDocument(array('name' => 'Luke Cawood')));
        $mongoDoc = array();
        $type->toMongo($doc, $mongoDoc, 'someDoc');
        $this->assertTrue(is_array($mongoDoc['someDoc']));
        $this->assertEquals('Luke Cawood', $mongoDoc['someDoc']['name']);
    }

    public function testEmbeddedDocumentFieldFromMongo()
    {
        $type = new Moa\Types\EmbeddedDocumentField(array('type'=>'MyDocument'));
        $doc = array();
        $mongoDoc = array('someDoc' => array('name' => 'Luke Cawood'));
        $type->fromMongo($doc, $mongoDoc, 'someDoc');
        $this->assertTrue($doc['someDoc'] instanceof MyDocument);
        $this->assertEquals('Luke Cawood', $doc['someDoc']->name);  
    }

    public function testReferenceField()
    {
        $type = new Moa\Types\ReferenceField(array('type'=>'MyModel'));
        $prop = new Moa\DomainObject\ReferenceProperty();
        $prop->set(123);
        $this->expectValidationFailure($type, $prop);
        $prop->set('dgfdf');
        $this->expectValidationFailure($type, $prop);
        $prop->set(array());
        $this->expectValidationFailure($type, $prop);
        $prop->set(true);
        $this->expectValidationFailure($type, $prop);
        $prop->set(new MyDocument());
        $this->expectValidationFailure($type, $prop);        
        $prop->set(null);
        $this->expectValidationSuccess($type, $prop);
        $prop->set(new MyModel());
        $this->expectValidationSuccess($type, $prop);

        $type = new Moa\Types\ReferenceField(array('type'=>'MyModel', 'required' => true));
        $prop = new Moa\DomainObject\ReferenceProperty();
        $prop->set(null);
        $this->expectValidationFailure($type, $prop);
        $prop->set(new MyModel());
        $this->expectValidationSuccess($type, $prop);        
    }

    public function testReferenceFieldToMongo()
    {
        $type = new Moa\Types\ReferenceField(array('type'=>'MyModel'));
        $prop = new Moa\DomainObject\ReferenceProperty();
        $prop->set(new MyModel(array('_id' => 100, 'name' => 'Luke Cawood')));
        $doc = array('someDoc' => $prop);
        $mongoDoc = array();
        $type->toMongo($doc, $mongoDoc, 'someDoc');
        $this->assertEquals($mongoDoc['someDoc'], 100);
        $this->assertEquals($mongoDoc['someDoc__type'], 'MyModel');
    }

    public function testReferenceFieldFromMongo()
    {
        $type = new Moa\Types\ReferenceField(array('type'=>'MyModel'));
        $doc = array();
        $mongoDoc = array('someDoc' => 100, 'someDoc__type' => 'MyModel');
        $type->fromMongo($doc, $mongoDoc, 'someDoc');

        $identity = $doc['someDoc']->getIdentity();

        $this->assertEquals($identity['id'], 100);
        $this->assertEquals($identity['type'], 'MyModel');
    }

    private function expectValidationFailure($type, $value)
    {
        try
        {
            $type->validate($value);
            if (is_object($value))
                $value = get_class($value);         
            $this->fail('Expected validation to fail for type '.get_class($type).' with value '.$value);
        }
        catch (Moa\Types\TypeException $e)
        {

        }
    }

    private function expectValidationSuccess($type, $value)
    {
        try
        {
            $type->validate($value);
        }
        catch (Moa\Types\TypeException $e)
        {
            if (is_object($value))
                $value = get_class($value);         
            $this->fail('Expected validation to succeed for type '.get_class($type).' with value '.$value);
        }
    }
}