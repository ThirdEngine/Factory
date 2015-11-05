<?php
/**
 * This class will handle object creation to allow the injection of test doubles.
 *
 * Usage:
 *
 * Factory is used to create objects in a standard way that allow for easy replacement with test double from
 * unit tests. This has only been tested with phpunit, but there isn't a reason it should not be able to work
 * with other unit testing frameworks.
 *
 *
 * // This is equivalent to $newObject = new NewObjectClass(); in your normal code execution
 * $newObject = Factory::createNewObject(NewObjectClass::class);
 * $newObject->standardOperations();
 *
 * Then when you are in a unit test, there is a simple method to replace $newObject with a test double.
 *
 * // $newObjectMock is already a mock object, and you want it to take the place of $newObject
 * Factory::injectObject(NewObjectClass::class, $newObjectMock);
 *
 *
 * In many cases, your constructors will take parameters. With Factory, it is simple to pass parameters to object
 * constructors.
 *
 * // This is equivalent to $newObject = new NewObjectClass($someOtherObject);
 * $newObject = Factory::createNewObject(NewObjectClass::class, [$someOtherObject]);
 * $newObject->standardOperations();
 *
 * Then when you are in a unit test, everything works the same way. $newObjectMock is already a mock object.
 * Factory::injectObject(NewObjectClass::class, $newObjectMock);
 *
 *
 * Factory also supports being able to create different instances of the same object. Many times you will need to
 * create the same object in multiple methods.
 *
 * $newObject1 = Factory::createNewObject(NewObjectClass::class);
 *
 * // a little while later
 * 4newObject2 = Factory::createNewObject(NewObjectClass::class);
 *
 * // In your test, assuming $newObject1Mock and $newObject2Mock are mock objects already. Use a zero-based index
 * // to order the injections you want.
 * Factory::injectObject(NewObjectClass::class, $newObject1Mock, 0);
 * Factory::injectObject(NewObjectClass::class, $newObject2Mock, 1);
 *
 *
 * @author Tony Vance, Third Engine Software
 */
namespace ThirdEngine\Factory;

use ReflectionClass;


class Factory
{
    /**
     * This contains injected mocks for objects.
     *
     * @var array
     */
    protected static $injectedObjects = [];

    /**
     * This contains injected mocks for query objects.
     *
     * @var array
     */
    protected static $injectedQueryObjects = [];

    /**
     * This contains the number of times mocks have been retrieved. This allows us to
     * supply multiple mocks of the same type.
     *
     * @var array
     */
    protected static $usedMockCounts = [
        'object' => [],
        'query' => [],
    ];


    /**
     * This method will cleanup all current data so that we can run clean tests without side effects from other tests.
     */
    public static function cleanup()
    {
        self::$injectedObjects = [];
        self::$injectedQueryObjects = [];
        self::$usedMockCounts = [
            'object' => [],
            'query' => [],
        ];
    }

    /**
     * This method will inject a mock to use instead of an object for unit testing.
     *
     * @param string $objectName
     * @param object $mock
     * @param int $index
     */
    public static function injectObject($objectName, $mock, $index = null)
    {
        self::injectObjectToArray(self::$injectedObjects, $objectName, $mock, $index);
    }

    /**
     * This method will inject a mock to use instead of an object for unit testing.
     *
     * @param string $objectName
     * @param object $mock
     * @param int $index
     */
    public static function injectQueryObject($objectName, $mock, $index = null)
    {
        self::injectObjectToArray(self::$injectedQueryObjects, $objectName, $mock, $index);
    }

    /**
     * This method will inject an object into a specified array.
     *
     * @param array $injectedObjects
     * @param string $objectName
     * @param object $mock
     * @param int $index
     */
    protected static function injectObjectToArray(array &$injectedObjects, $objectName, $mock, $index)
    {
        if ($index === null)
        {
            $injectedObjects[$objectName] = $mock;
        }
        else
        {
            $injectedObjects[$objectName][$index] = $mock;
        }
    }

    /**
     * This method will determine if we should return a mock. If so, we will return the mock, if not we will
     * return null.
     *
     * @param array $injectedObjects
     * @param string $countsArray
     * @param string $className
     *
     * @return object
     */
    protected static function getMock(array &$injectedObjects, $countsArray, $className)
    {
        if (!isset($injectedObjects[$className]) || !$injectedObjects[$className])
        {
            return null;
        }

        if (!is_array($injectedObjects[$className]))
        {
            return $injectedObjects[$className];
        }

        $currentCount = isset(self::$usedMockCounts[$countsArray][$className]) ? self::$usedMockCounts[$countsArray][$className] : -1;
        ++ $currentCount;

        self::$usedMockCounts[$countsArray][$className] = $currentCount;
        return $injectedObjects[$className][$currentCount];
    }

    /**
     * This method will create a new object. This helps make our framework more testable.
     *
     * @param  $className
     * @param  $args
     * @return object
     */
    public static function createNewObject($className, $args = null)
    {
        $mock = self::getMock(self::$injectedObjects, 'object', $className);
        if ($mock !== null)
        {
            return $mock;
        }

        if ($args === null)
        {
            return new $className();
        }

        // we need to pass arguments which requires reflection
        $reflection = new ReflectionClass($className);
        return $reflection->newInstanceArgs($args);
    }

    /**
     * This method will create a new query object with the static create method. This
     * should be used when creating query objects even though createNewObject() will work. This is
     * to accommodate the proper practice using the Propel ORM to use QueryObject::create() instead of
     * new QueryObject().
     *
     * @param  $className
     * @param  $modelAlias
     * @param  $criteria
     * @return object
     */
    public static function createNewQueryObject($className, $modelAlias = null, $criteria = null)
    {
        $mock = self::getMock(self::$injectedQueryObjects, 'query', $className);
        if ($mock !== null)
        {
            return $mock;
        }

        return call_user_func([$className, 'create'], $modelAlias, $criteria);
    }
}