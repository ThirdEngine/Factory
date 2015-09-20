# factory

Factory is used to create objects in a standard way that allow for easy replacement with test double from
unit tests. This has only been tested with phpunit, but there isn't a reason it should not be able to work
with other unit testing frameworks.


// This is equivalent to $newObject = new NewObjectClass(); in your normal code execution
$newObject = Factory::createNewObject(NewObjectClass::class);
$newObject->standardOperations();

Then when you are in a unit test, there is a simple method to replace $newObject with a test double.

// $newObjectMock is already a mock object, and you want it to take the place of $newObject
Factory::injectObject(NewObjectClass::class, $newObjectMock);


In many cases, your constructors will take parameters. With Factory, it is simple to pass parameters to object
constructors.

// This is equivalent to $newObject = new NewObjectClass($someOtherObject);
$newObject = Factory::createNewObject(NewObjectClass::class, [$someOtherObject]);
$newObject->standardOperations();

Then when you are in a unit test, everything works the same way. $newObjectMock is already a mock object.
Factory::injectObject(NewObjectClass::class, $newObjectMock);


Factory also supports being able to create different instances of the same object. Many times you will need to
create the same object in multiple methods.

$newObject1 = Factory::createNewObject(NewObjectClass::class);

// a little while later
4newObject2 = Factory::createNewObject(NewObjectClass::class);

// In your test, assuming $newObject1Mock and $newObject2Mock are mock objects already. Use a zero-based index
// to order the injections you want.
Factory::injectObject(NewObjectClass::class, $newObject1Mock, 0);
Factory::injectObject(NewObjectClass::class, $newObject2Mock, 1);
