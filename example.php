<?php 

require_once('vendor/autoload.php');


class TestDocument extends Moa\DomainObject
{
    public function properties()
    {
        return array(
            'myInt' => new Moa\Types\IntegerField(array('required' => true)),            
            'myString' => new Moa\Types\StringField(),
            'myArray' => new Moa\Types\ArrayField(),
            'myOwnSelf' => new Moa\Types\EmbeddedDocumentField(array('type'=>'TestDocument')),
        );
    }
}

$conn = new Mongo();
Moa::setup($conn->moademo);

TestDocument::remove();

$doc = new TestDocument(array(
	'myInt' => 100
));

$doc->save();

$doc->myOwnSelf = new TestDocument(array(
	'myInt' => 2200
));;

$doc->save();


var_dump(iterator_to_array(TestDocument::find()));