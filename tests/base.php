<?php

require_once(__DIR__.'/../vendor/autoload.php');

date_default_timezone_set('Australia/Melbourne');

error_reporting(E_ALL);

class MyModel extends Moa\DomainObject
{

}

class MyDocument extends Moa\Document
{

}

class MoaTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }
}
