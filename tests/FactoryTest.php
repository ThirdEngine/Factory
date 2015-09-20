<?php
namespace ThirdEngine\Factory;

use stdClass;
use PHPUnit_Framework_TestCase;


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
}