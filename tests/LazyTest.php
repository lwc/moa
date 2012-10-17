<?php

require_once(__DIR__.'/base.php');


class LazyTest extends MoaTest
{
    public function testDefaults()
    {
        $prop = $this->createProperty();
        $this->assertEquals(null, $prop->getIdentity());
        $this->assertEquals(null, $prop->get());
    }

    public function testCreatedIdentityIsCached()
    {
        $prop = $this->createProperty();

        $prop->set((object)array('name'=>'Luke', 'age' => 29));

        // Nothing should have happened yet
        $this->assertEquals(0, $prop->callCount('createIdentity'));

        // get the identity, causing it to be created
        $identity = $prop->getIdentity();
        $this->assertEquals(1, $prop->callCount('createIdentity'));
        $this->assertEquals(29, $identity['age']);

        // should still be referenced
        $identity = $prop->getIdentity();
        $this->assertEquals(1, $prop->callCount('createIdentity'));
    }

    public function testSettingIdentity()
    {
        $prop = $this->createProperty();

        // No instance, there should be no need for comparison
        $prop->setIdentity(array(10));
        $this->assertEquals(0, $prop->callCount('equals'));
        $this->assertEquals(array(10), $prop->getIdentity());
    }

    public function testSettingIdentityExpiresInstanceCache()
    {
        $prop = $this->createProperty();

        // Instance does not match new identity set
        $prop->set((object)array(20));
        $prop->setIdentity(array(10));
        $this->assertEquals(1, $prop->callCount('equals'));

        // So, on the next get() a new instance should be loaded
        $this->assertEquals(0, $prop->callCount('loadInstance'));
        $prop->get();
        $this->assertEquals(1, $prop->callCount('loadInstance'));
    }

    public function testSettingIdentityKeepsSameInstance()
    {
        $prop = $this->createProperty();

        // Instance matches the new identity set
        $prop->set((object)array(10));
        $prop->setIdentity(array(10));
        $this->assertEquals(1, $prop->callCount('equals'));

        // So, on the next get() nothing needs to be loaded
        $this->assertEquals(0, $prop->callCount('loadInstance'));
        $prop->get();
        $this->assertEquals(0, $prop->callCount('loadInstance'));
    }

    public function testHasValue()
    {
        $prop = $this->createProperty();
        $this->assertFalse($prop->hasValue());

        $prop->set(true);
        $this->assertTrue($prop->hasValue());

        $prop->del();
        $this->assertFalse($prop->hasValue());

        $prop->setIdentity(true);
        $this->assertTrue($prop->hasValue());
    }

    public function testDel()
    {
        $prop = $this->createProperty();

        $prop->set(true);
        $this->assertTrue($prop->hasValue());
        $this->assertEquals(true, $prop->get());

        $prop->del();
        $this->assertNull($prop->get());        
        $this->assertFalse($prop->hasValue());

        $prop->setIdentity(true);
        $this->assertTrue($prop->hasValue());
        $this->assertEquals(true, $prop->getIdentity());

        $prop->del();
        $this->assertNull($prop->getIdentity());        
        $this->assertFalse($prop->hasValue());
    }

    private function createProperty()
    {
        return new TestProperty();
    }
}


class TestProperty extends Moa\DomainObject\LazyProperty
{
    private $counts = array();

    protected function equals($instance, $identity)
    {
        $this->incrementCount(__FUNCTION__);
        return (array)$instance == $identity;
    }

    protected function createIdentity($instance)
    {
        $this->incrementCount(__FUNCTION__);
        return (array)$instance;
    }

    protected function loadInstance($identity)
    {
        $this->incrementCount(__FUNCTION__);
        return (object)$identity;
    }

    public function callCount($method)
    {
        if (isset($this->counts[$method]))
            return $this->counts[$method];
        return 0;
    }

    private function incrementCount($name)
    {
        if (!isset($this->counts[$name]))
            $this->counts[$name] = 1;
        else
            $this->counts[$name]++;
    }
}
