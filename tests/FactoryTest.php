<?php
namespace ThirdEngine\Factory;

use stdClass;
use PHPUnit_Framework_TestCase;


class HelperObject
{
  public $property;

  public function __construct($propertyValue)
  {
    $this->property = $propertyValue;
  }
}


class FactoryTest extends PHPUnit_Framework_TestCase
{
  public static function create()
  {
    return 'TestValue';
  }

  public function testCreateNewQueryObjectCallsStaticCreate()
  {
    $this->assertEquals('TestValue', Factory::createNewQueryObject(self::class));
  }

  public function testCleanupClearsData()
  {
    $mock = $this->getMock(stdClass::class, []);
    Factory::injectObject(stdClass::class, $mock);

    $object = Factory::createNewObject(stdClass::class);
    $this->assertEquals($mock, $object);

    Factory::cleanup();

    $object = Factory::createNewObject(stdClass::class);
    $this->assertNotEquals($mock, $object);
  }

  public function testInjectQueryObjectCausesMockToReturnFromCreateNewQueryObject()
  {
    $queryMock = $this->getMock(stdClass::class, []);
    Factory::injectQueryObject(stdClass::class, $queryMock);

    $this->assertEquals($queryMock, Factory::createNewQueryObject(stdClass::class));
  }

  public function testInjectObjectWithIndexesCausesSequentialReturnsFromCreateNewObject()
  {
    $mock1 = $this->getMock(stdClass::class, []);
    $mock1->property = 'mock1';

    $mock2 = $this->getMock(stdClass::class, []);
    $mock2->property = 'mock2';

    Factory::injectObject(stdClass::class, $mock1, 0);
    Factory::injectObject(stdClass::class, $mock2, 1);

    $this->assertEquals($mock1, Factory::createNewObject(stdClass::class));
    $this->assertEquals($mock2, Factory::createNewObject(stdClass::class));
  }

  public function testCreateNewObjectProperlySuppliesConstructorParametersWhenNotMocked()
  {
    $propertyValue = 'Hello World!';

    $helperObject = Factory::createNewObject(HelperObject::class, [$propertyValue]);
    $this->assertEquals($propertyValue, $helperObject->property);
  }
}