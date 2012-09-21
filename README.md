## Moa: the rightweight mongoDB ODM

Moa is an Object - Document Mapper, built to persist business domain objects in mongoDB.

### Why?
- Current PHP solutions in this space fall into one of two categories: they are either massive bloated abstractions or they dont do anything useful
- Moa built around the concept that mongo query tools are already pretty good, all that's missing is some validation and typed de/serialisation
- Built to integrate easily into existing applications
- Easy to create custom types with de/serialisation behaviours and validation rules


### Installation

- Install via composer :TODO:
- via github https://github.com/99designs/moa

### Getting Started

Somewhere in your app start up code, add

```php
<?php

$connection = new Mongo(); // or whatever
Moa::setup($connection, 'my_database');
```

### Defining Models

Model classes can be defined like so:

```php
<?php

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
```

For a full list of field types and their behaviours, see SOMEURL


### Querying

The query syntax is the same as the default PHP mongo driver, but accessed statically from the domain object you hope to query, eg

```php
<?php

$docs = TestDocument::find(array('myString'=>'value'));

// it is also possible to use cursor methods
$docs = TestDocument::find(array('myString'=>'value'))->skip(20)->limit(10);

// findOne also works
$doc = TestDocument::findOne(array('myString'=>'value')); // this could except

// Documents may be saved via a call to save()
$doc->myInt = 123;
$doc->save();
```


