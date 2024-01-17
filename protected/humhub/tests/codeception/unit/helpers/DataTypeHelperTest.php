<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2018-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace humhub\tests\codeception\unit\helpers;

use Codeception\Test\Feature\Stub;
use Codeception\Test\Unit;
use Codeception\TestInterface;
use humhub\exceptions\InvalidArgumentClassException;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use humhub\helpers\DataTypeHelper;
use PHPUnit\Framework\Exception;
use stdClass;
use Stringable;
use yii\base\ArrayableTrait;
use yii\base\BaseObject;
use yii\base\Configurable;
use yii\base\Model;

/**
 * Class DataTypeHelperTest
 */
class DataTypeHelperTest extends Unit
{
    public function testClassTypeHelperCase1()
    {
        static::assertNull(DataTypeHelperMock::checkTypeHelper($value, '', null));
        static::assertNull(DataTypeHelperMock::checkTypeHelper($value, '', 1));
        static::assertNull(DataTypeHelperMock::checkTypeHelper($value, '', 1.2));
        static::assertNull(DataTypeHelperMock::checkTypeHelper($value, '', true));
    }

    public function testClassTypeHelperCase2()
    {
        $handle = fopen('php://memory', 'ab');
        fclose($handle);

        $tests = [
            'boolean' => [
                true,
                'bool'
            ],
            'integer' => [
                1,
                'int'
            ],
            'string' => [
                '',
            ],
            'array' => [
                [],
            ],
            'object' => [
                new stdClass(),
            ],
            'resource' => [
                fopen('php://memory', 'ab'),
            ],
            'resource (closed)' => [
                $handle,
            ],
            'NULL' => [
                null,
            ],
            'float' => [
                1.2,
                'double'
            ],
        ];

        $values = array_combine(array_keys($tests), array_column($tests, 0));

        foreach ($tests as $key => $test) {
            codecept_debug("- Testing $key");

            $current = gettype($test[0]);

            static::assertEquals($key, DataTypeHelperMock::checkTypeHelper($value, $current, $key));

            if (array_key_exists(1, $test)) {
                static::assertEquals($test[1], DataTypeHelperMock::checkTypeHelper($value, $current, $test[1]));
            }

            foreach ($values as $i => $type) {
                if ($i === $key) {
                    continue;
                }

                $current = gettype($type);
                static::assertNull(DataTypeHelperMock::checkTypeHelper($value, $current, $key));
            }
        }
    }

    public function testClassTypeHelperCase3()
    {
        $value = new class () implements Stringable {
            public function __toString(): string
            {
                return '';
            }
        };

        static::assertEquals('object', DataTypeHelperMock::checkTypeHelper($value, 'object', 'object'));
        static::assertEquals(
            Stringable::class,
            DataTypeHelperMock::checkTypeHelper($value, 'object', Stringable::class)
        );

        $value = new class () {
            public function __toString(): string
            {
                return '';
            }
        };

        static::assertEquals(
            'object',
            DataTypeHelperMock::checkTypeHelper($value, gettype($value), 'object')
        );
        static::assertEquals(
            Stringable::class,
            DataTypeHelperMock::checkTypeHelper($value, 'object', Stringable::class)
        );

        $value = new static();

        static::assertEquals(
            'object',
            DataTypeHelperMock::checkTypeHelper($value, gettype($value), 'object')
        );

        // test class
        static::assertEquals(
            static::class,
            DataTypeHelperMock::checkTypeHelper($value, gettype($value), static::class)
        );

        // test interface
        static::assertEquals(
            TestInterface::class,
            DataTypeHelperMock::checkTypeHelper($value, gettype($value), TestInterface::class)
        );

        // test trait
        static::assertEquals(
            Stub::class,
            DataTypeHelperMock::checkTypeHelper($value, gettype($value), Stub::class)
        );
    }

    public function testParseTypeCase1()
    {
        static::assertEquals(['NULL'], DataTypeHelperMock::parseTypes(null));
        static::assertEquals(['test'], DataTypeHelperMock::parseTypes(['test']));
        static::assertEquals(['test'], DataTypeHelperMock::parseTypes('test'));
        static::assertEquals(['foo', 'bar'], DataTypeHelperMock::parseTypes('foo|bar'));
    }

    public function testParseTypeCase2()
    {
        $message = '$types cannot be empty';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);

        DataTypeHelperMock::parseTypes('');
    }

    public function testParseTypeCase3()
    {
        $message = '$types cannot be empty';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);

        DataTypeHelperMock::parseTypes([]);
    }

    /**
     * @depends testClassTypeHelperCase1
     * @depends testClassTypeHelperCase2
     * @depends testClassTypeHelperCase3
     * @depends testParseTypeCase1
     * @depends testParseTypeCase2
     * @depends testParseTypeCase3
     */
    public function testCheckTypeCase()
    {
        $tests = [
            'boolean' => true,
            'bool' => true,
            'integer' => 1,
            'int' => 1,
            'string' => '',
            'array' => [],
            'object' => new stdClass(),
            'resource' => fopen('php://memory', 'ab'),
            'NULL' => null,
            'float' => 1.2,
            'double' => 1.2,
        ];

        foreach ($tests as $key => $value) {
            codecept_debug("- Testing $key");

            static::assertEquals($key, DataTypeHelper::checkType($value, [$key]));
        }

        static::assertEquals('string', DataTypeHelper::checkType('', [null, 'string']));
        static::assertEquals('string', DataTypeHelper::checkType('', ['string', null]));
        static::assertEquals('string', DataTypeHelper::checkType('', ['string', 'NULL']));

        $values = [
            new class () implements Stringable {
                public function __toString(): string
                {
                    return '';
                }
            },
            new class () {
                public function __toString(): string
                {
                    return '';
                }
            }
        ];

        foreach ($values as $value) {
            static::assertEquals('object', DataTypeHelper::checkType($value, ['object']));
            static::assertEquals(
                Stringable::class,
                DataTypeHelper::checkType($value, [Stringable::class])
            );
            static::assertEquals('is_object', DataTypeHelper::checkType($value, ['is_object']));

            // type order is of significance, if multiple types match
            static::assertEquals(
                'object',
                DataTypeHelper::checkType($value, ['object', Stringable::class])
            );
            static::assertEquals(
                Stringable::class,
                DataTypeHelper::checkType($value, [Stringable::class, 'object'])
            );
        }
    }

    /**
     * @depends testCheckTypeCase
     */
    public function testFilterBool()
    {
        static::assertTrue(DataTypeHelper::filterBool(true, true));
        static::assertNull(DataTypeHelper::filterBool('true', true));
        static::assertNull(DataTypeHelper::filterBool(1, true));

        static::assertFalse(DataTypeHelper::filterBool(false, true));
        static::assertNull(DataTypeHelper::filterBool('false', true));
        static::assertNull(DataTypeHelper::filterBool(0, true));

        static::assertTrue(DataTypeHelper::filterBool(true));
        static::assertTrue(DataTypeHelper::filterBool('true'));
        static::assertTrue(DataTypeHelper::filterBool(1));
        static::assertTrue(DataTypeHelper::filterBool('1'));

        static::assertTrue(DataTypeHelper::filterBool(true, null));
        static::assertTrue(DataTypeHelper::filterBool(1, null));
        static::assertTrue(DataTypeHelper::filterBool('1', null));
        static::assertTrue(DataTypeHelper::filterBool('foo', null));
        static::assertTrue(DataTypeHelper::filterBool(['1'], null));
        static::assertFalse(DataTypeHelper::filterBool(false, null));
        static::assertFalse(DataTypeHelper::filterBool('false', null));
        static::assertFalse(DataTypeHelper::filterBool('0', null));
        static::assertFalse(DataTypeHelper::filterBool('', null));
        static::assertFalse(DataTypeHelper::filterBool(0, null));
        static::assertFalse(DataTypeHelper::filterBool([], null));
        static::assertFalse(DataTypeHelper::filterBool(null, null));
    }

    /**
     * @depends testCheckTypeCase
     */
    public function testFilterFloat()
    {
        static::assertEquals(1.1, DataTypeHelper::filterFloat(1.1, true));
        static::assertNull(DataTypeHelper::filterFloat('1.1', true));

        static::assertEquals(1.0, DataTypeHelper::filterFloat(1));
        static::assertEquals(1.0, DataTypeHelper::filterFloat('1'));
        static::assertEquals(1.1, DataTypeHelper::filterFloat(1.1));
        static::assertEquals(1.1, DataTypeHelper::filterFloat('1.1'));
        static::assertNull(DataTypeHelper::filterFloat('1.1.3'));
    }

    /**
     * @depends testCheckTypeCase
     */
    public function testFilterInt()
    {
        static::assertEquals(1, DataTypeHelper::filterInt(1, true));
        static::assertNull(DataTypeHelper::filterInt('1', true));

        static::assertEquals(1, DataTypeHelper::filterInt(1));
        static::assertEquals(1, DataTypeHelper::filterInt('1'));
        static::assertNull(DataTypeHelper::filterInt('1.1'));
    }

    /**
     * @depends testCheckTypeCase
     */
    public function testFilterScalar()
    {
        static::assertEquals('', DataTypeHelper::filterScalar('', true));
        static::assertEquals(1, DataTypeHelper::filterScalar(1, true));

        static::assertNull(DataTypeHelper::filterScalar(null, true));
        static::assertNull(DataTypeHelper::filterScalar(fopen('php://memory', 'ab'), true));

        static::assertEquals('', DataTypeHelper::filterScalar(''));
        static::assertEquals(1, DataTypeHelper::filterScalar(1));

        static::assertNull(DataTypeHelper::filterScalar(null));
        static::assertNull(DataTypeHelper::filterScalar(fopen('php://memory', 'ab')));
    }

    /**
     * @depends testCheckTypeCase
     */
    public function testFilterString()
    {
        static::assertEquals('', DataTypeHelper::filterString('', true));
        static::assertEquals('1', DataTypeHelper::filterString('1', true));
        static::assertNull(DataTypeHelper::filterString(1, true));

        static::assertEquals('', DataTypeHelper::filterString(''));
        static::assertEquals('1', DataTypeHelper::filterString('1'));
        static::assertEquals('1', DataTypeHelper::filterString(1));
        static::assertEquals('1', DataTypeHelper::filterString('1'));
        static::assertEquals('1.1', DataTypeHelper::filterString('1.1'));
        static::assertEquals('1.1', DataTypeHelper::filterString(1.1));

        $value = new class () implements Stringable {
            public function __toString(): string
            {
                return 'foo';
            }
        };
        static::assertEquals('foo', DataTypeHelper::filterString($value));

        $value = new class () {
            public function __toString(): string
            {
                return 'bar';
            }
        };
        static::assertEquals('bar', DataTypeHelper::filterString($value));

        static::assertNull(DataTypeHelper::filterString([]));
        static::assertNull(DataTypeHelper::filterString((object)[]));
    }

    public function testClassTypeCheckCase1()
    {
        $message = 'Argument $type passed to humhub\helpers\DataTypeHelper::filterClassType must be one of string, string[] - NULL given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::CLASS_CHECK_VALUE_IS_EMPTY);

        DataTypeHelper::filterClassType(null, null);
    }

    public function testClassTypeCheckCase2()
    {
        $message = 'Argument $type passed to humhub\helpers\DataTypeHelper::filterClassType must be one of string, string[] - empty string given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::CLASS_CHECK_VALUE_IS_EMPTY);

        DataTypeHelper::filterClassType(null, '');
    }

    public function testClassTypeCheckCase3()
    {
        $message = 'Argument $type passed to humhub\helpers\DataTypeHelper::filterClassType must be one of string, string[] - [] given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::CLASS_CHECK_VALUE_IS_EMPTY);

        DataTypeHelper::filterClassType(null, []);
    }

    public function testClassTypeCheckCase4()
    {
        $message = 'Argument $type passed to humhub\helpers\DataTypeHelper::filterClassType must be one of string, string[] - 0 given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::CLASS_CHECK_VALUE_IS_EMPTY);

        DataTypeHelper::filterClassType(null, 0);
    }

    public function testClassTypeCheckCase5()
    {
        $message = 'Argument $type passed to humhub\helpers\DataTypeHelper::filterClassType must be one of string, string[] - \'0\' given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::CLASS_CHECK_VALUE_IS_EMPTY);

        DataTypeHelper::filterClassType(null, '0');
    }


    public function testClassTypeCheckCase6()
    {
        $message = 'Argument $type[0] passed to humhub\helpers\DataTypeHelper::filterClassType must be a valid class/interface/trait name or an object instance - humhub\tests\codeception\unit\helpers\NonExistingClassName given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::CLASS_CHECK_NON_EXISTING_CLASS);

        /** @noinspection PhpUndefinedClassInspection */
        DataTypeHelper::filterClassType(null, NonExistingClassName::class);
    }

    public function testClassTypeCheckCase7()
    {
        $message = 'Argument $type[1] passed to humhub\helpers\DataTypeHelper::filterClassType must be a valid class/interface/trait name or an object instance - humhub\tests\codeception\unit\helpers\NonExistingClassName given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::CLASS_CHECK_NON_EXISTING_CLASS);

        /** @noinspection PhpUndefinedClassInspection */
        DataTypeHelper::filterClassType(null, [BaseObject::class, NonExistingClassName::class]);
    }

    public function testClassTypeCheckCaseNull()
    {
        static::assertNull(
            DataTypeHelper::filterClassType(null, BaseObject::class, false)
        );

        static::assertNull(
            DataTypeHelper::filterClassType(null, [BaseObject::class, null])
        );

        $message = 'Argument $className passed to humhub\helpers\DataTypeHelper::filterClassType must be of type yii\base\BaseObject - NULL given.';

        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + DataTypeHelper::CLASS_CHECK_VALUE_IS_EMPTY + DataTypeHelper::CLASS_CHECK_INVALID_TYPE + DataTypeHelper::CLASS_CHECK_VALUE_IS_NULL);

        DataTypeHelper::filterClassType(null, BaseObject::class);
    }

    public function testClassTypeCheckCaseEmptyString()
    {
        static::assertNull(
            DataTypeHelper::filterClassType('', BaseObject::class, false)
        );

        static::assertNull(
            DataTypeHelper::filterClassType('', [BaseObject::class, null], true, false)
        );

        $message = 'Argument $className passed to humhub\helpers\DataTypeHelper::filterClassType must be of type yii\base\BaseObject - empty string given.';

        $this->expectException(InvalidArgumentClassException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + DataTypeHelper::CLASS_CHECK_VALUE_IS_EMPTY + DataTypeHelper::CLASS_CHECK_INVALID_TYPE + DataTypeHelper::CLASS_CHECK_TYPE_NOT_IN_LIST);

        DataTypeHelper::filterClassType('', BaseObject::class);
    }

    public function testClassTypeCheckCaseString()
    {
        /** @noinspection PhpUndefinedClassInspection */
        static::assertNull(
            DataTypeHelper::filterClassType(NonExistingClassName::class, BaseObject::class, false)
        );

        $message = 'Argument $className passed to humhub\helpers\DataTypeHelper::filterClassType must be a valid class name or an object instance - humhub\tests\codeception\unit\helpers\NonExistingClassName given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + DataTypeHelper::CLASS_CHECK_NON_EXISTING_CLASS);

        /** @noinspection PhpUndefinedClassInspection */
        DataTypeHelper::filterClassType(NonExistingClassName::class, BaseObject::class);
    }

    public function testClassTypeCheckCaseWrongClass()
    {
        static::assertNull(
            DataTypeHelper::filterClassType(Exception::class, BaseObject::class, false)
        );

        $message = 'Argument $className passed to humhub\helpers\DataTypeHelper::filterClassType must be of type yii\base\BaseObject - PHPUnit\Framework\Exception given.';

        $this->expectException(InvalidArgumentClassException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + DataTypeHelper::CLASS_CHECK_TYPE_NOT_IN_LIST);

        DataTypeHelper::filterClassType(Exception::class, BaseObject::class);
    }

    public function testClassTypeCheckCaseWrongInstance()
    {
        static::assertNull(
            DataTypeHelper::filterClassType(new Exception('hello'), BaseObject::class, false)
        );

        $message = 'Argument $className passed to humhub\helpers\DataTypeHelper::filterClassType must be of type yii\base\BaseObject - PHPUnit\Framework\Exception given.';

        $this->expectException(InvalidArgumentClassException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + DataTypeHelper::CLASS_CHECK_TYPE_NOT_IN_LIST + DataTypeHelper::CLASS_CHECK_VALUE_IS_INSTANCE);

        DataTypeHelper::filterClassType(new Exception('hello'), BaseObject::class);
    }

    public function testClassTypeCheckCaseCorrectClass()
    {
        static::assertEquals(
            Exception::class,
            DataTypeHelper::filterClassType(Exception::class, Exception::class)
        );

        static::assertEquals(
            Exception::class,
            DataTypeHelper::filterClassType(Exception::class, [new \Exception()])
        );

        static::assertEquals(
            BaseObject::class,
            DataTypeHelper::filterClassType(BaseObject::class, Configurable::class)
        );

        static::assertEquals(
            Model::class,
            DataTypeHelper::filterClassType(Model::class, ArrayableTrait::class)
        );

        static::assertEquals(
            Exception::class,
            DataTypeHelper::filterClassType('#%' . Exception::class, Exception::class, false, false)
        );

        static::assertNull(
            DataTypeHelper::filterClassType('#%' . Exception::class, Exception::class, false, true)
        );

        $message = 'Argument $className passed to humhub\helpers\DataTypeHelper::filterClassType must be a valid class name or an object instance - #%PHPUnit\Framework\Exception given.';

        $this->expectException(InvalidArgumentClassException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER);

        DataTypeHelper::filterClassType('#%' . Exception::class, Exception::class);
    }

    public function testClassTypeCheckCaseCorrectInstance()
    {
        static::assertEquals(
            Exception::class,
            DataTypeHelper::filterClassType(new Exception('hello'), Exception::class)
        );

        static::assertEquals(
            Exception::class,
            DataTypeHelper::filterClassType(new Exception('hello'), [new \Exception()])
        );

        static::assertEquals(
            BaseObject::class,
            DataTypeHelper::filterClassType(new BaseObject(), Configurable::class)
        );

        static::assertEquals(
            Model::class,
            DataTypeHelper::filterClassType(new Model(), ArrayableTrait::class)
        );
    }
}
