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
        );
    }
}

class DocumentTest extends MoaTest
{
    public function testValidate()
    {
        $doc = new TestDocument(array(
            'myInt' => 23,
            'myOwnSelf' => new TestDocument(array(
                'myInt' => 6
            )),
            'thisisnew' => 34534534
        ));
        $doc->validate();
        $mongoDoc = $doc->toMongo();

        $doc = new TestDocument();
        $doc->fromMongo($mongoDoc);
        $doc->validate();
    }
}