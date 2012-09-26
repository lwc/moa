<?php

namespace Moa\DomainObject;
use \Moa;

interface LazyProperty
{
    public function setIdentity($identity);

    public function getIdentity();

    public function get();

    public function set($value);
}