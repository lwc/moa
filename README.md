## Moa: the rightweight mongoDB ODM

Moa is an Object - Document Mapper, built to persist business domain objects in mongoDB.

[![Build Status](https://travis-ci.org/99designs/moa.png)](https://travis-ci.org/99designs/moa)


### Why?
- Does the bare minimum to be useful
- Mongo query methods are already pretty good, all that's missing is some validation and typed de/serialisation
- Built to integrate easily into existing applications
- Zero dependencies
- Well tested
- Easy to create custom types with de/serialisation behaviours and validation rules


### Installation

- Install via composer: `composer require 99designs/moa`
- via github https://github.com/99designs/moa

### Getting Started

In your app start up code, add something that looks like the following:

```php
<?php

$connection = new Mongo(); // or whatever
Moa::setup($connection->mydatabase);
```

Optionally, a callback can be provided to lazily connect on demand:

```php
<?php

Moa::setup(function() {
    $connection = new Mongo();
    return $connection->someDb; 
});
```

Also, extra databases can be configured:

```php
<?php

$connection = new Mongo(); // or whatever
Moa::setup($connection->mydatabase);
Moa::instance()->addDatabase('anotherDb', $connection->differentDb); // also takes a callback
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

- For a full list of field types and their behaviours, see the Moa\Types namespace
- Indexes may also be defined (override `DomainObject::indexes()`)
- Domain objects can also specify the database they want to persist to (override `DomainObject::getDatabaseName()`)

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

// Documents can be deleted
TestDocument::remove(array('myString' => 'value')); // Deletes all documents with a field 'myString' with value of 'value'
```


