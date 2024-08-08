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
        static::assertNull(DataTypeHelperMock::matchTypeHelper(null, $value, ''));
        static::assertNull(DataTypeHelperMock::matchTypeHelper(1, $value, ''));
        static::assertNull(DataTypeHelperMock::matchTypeHelper(1.2, $value, ''));
        static::assertNull(DataTypeHelperMock::matchTypeHelper(true, $value, ''));
    }

    public function testClassTypeHelperCase2()
    {
        $handle = fopen('php://memory', 'ab');
        fclose($handle);

        $tests = [
            'boolean' => [
                true,
                'bool',
            ],
            'integer' => [
                1,
                'int',
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
                'double',
            ],
        ];

        $values = array_combine(array_keys($tests), array_column($tests, 0));

        foreach ($tests as $key => $test) {
            codecept_debug("- Testing $key");

            $current = gettype($test[0]);

            static::assertEquals($key, DataTypeHelperMock::matchTypeHelper($key, $value, $current));

            if (array_key_exists(1, $test)) {
                static::assertEquals($test[1], DataTypeHelperMock::matchTypeHelper($test[1], $value, $current));
            }

            foreach ($values as $i => $type) {
                if ($i === $key) {
                    continue;
                }

                $current = gettype($type);
                static::assertNull(DataTypeHelperMock::matchTypeHelper($key, $value, $current));
            }
        }
    }

    public function testClassTypeHelperCase3()
    {
        $value = new class implements Stringable {
            public function __toString(): string
            {
                return '';
            }
        };

        static::assertEquals('object', DataTypeHelperMock::matchTypeHelper('object', $value, 'object'));
        static::assertEquals(
            Stringable::class,
            DataTypeHelperMock::matchTypeHelper(Stringable::class, $value, 'object'),
        );

        $value = new class {
            public function __toString(): string
            {
                return '';
            }
        };

        static::assertEquals(
            'object',
            DataTypeHelperMock::matchTypeHelper('object', $value, gettype($value)),
        );
        static::assertEquals(
            Stringable::class,
            DataTypeHelperMock::matchTypeHelper(Stringable::class, $value, 'object'),
        );

        $value = new static();

        static::assertEquals(
            'object',
            DataTypeHelperMock::matchTypeHelper('object', $value, gettype($value)),
        );

        // test class
        static::assertEquals(
            static::class,
            DataTypeHelperMock::matchTypeHelper(static::class, $value, gettype($value)),
        );

        // test interface
        static::assertEquals(
            TestInterface::class,
            DataTypeHelperMock::matchTypeHelper(TestInterface::class, $value, gettype($value)),
        );

        // test trait
        $traits = DataTypeHelper::classUsesTraits($value);
        static::assertEquals(
            Stub::class,
            DataTypeHelperMock::matchTypeHelper(Stub::class, $value, gettype($value), $traits),
        );
    }

    public function testParseTypeCase1()
    {
        $types = null;
        static::assertEquals([], DataTypeHelperMock::parseTypes($types));
        static::assertEquals([null], $types);

        $types = ['string'];
        static::assertEquals(['string'], DataTypeHelperMock::parseTypes($types));
        static::assertEquals(['string'], $types);

        $types = 'int';
        static::assertEquals(['int'], DataTypeHelperMock::parseTypes($types));
        static::assertEquals(['int'], $types);

        $types = 'string|int';
        static::assertEquals(['string', 'int'], DataTypeHelperMock::parseTypes($types));
        static::assertEquals(['string', 'int'], $types);
    }

    public function testParseTypeCase2()
    {
        $message = 'Argument $allowedTypes passed to humhub\helpers\DataTypeHelper::parseTypes must be one of string, string[], object[] - empty string given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);

        $types = '';
        DataTypeHelperMock::parseTypes($types);
    }

    public function testParseTypeCase3()
    {
        $message = 'Argument $allowedTypes passed to humhub\helpers\DataTypeHelper::parseTypes must be one of string, string[], object[] - [] given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);

        $types = [];
        DataTypeHelperMock::parseTypes($types);
    }

    public function testParseTypeCase4()
    {
        $message = 'Argument $allowedTypes[0] passed to humhub\helpers\DataTypeHelper::parseTypes must be a valid class/interface/trait name or an object instance - test given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);

        $types = ['test'];
        DataTypeHelperMock::parseTypes($types);
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

            static::assertEquals($key, DataTypeHelper::matchType($value, [$key]));
        }

        static::assertEquals('string', DataTypeHelper::matchType('', [null, 'string']));
        static::assertEquals('string', DataTypeHelper::matchType('', ['string', null]));
        static::assertEquals('string', DataTypeHelper::matchType('', ['string', 'NULL']));

        $values = [
            new class implements Stringable {
                public function __toString(): string
                {
                    return '';
                }
            },
            new class {
                public function __toString(): string
                {
                    return '';
                }
            },
        ];

        foreach ($values as $value) {
            static::assertEquals('object', DataTypeHelper::matchType($value, ['object']));
            static::assertEquals(
                Stringable::class,
                DataTypeHelper::matchType($value, [Stringable::class]),
            );
            static::assertEquals('is_object', DataTypeHelper::matchType($value, ['is_object']));

            // type order is of significance, if multiple types match
            static::assertEquals(
                'object',
                DataTypeHelper::matchType($value, ['object', Stringable::class]),
            );
            static::assertEquals(
                Stringable::class,
                DataTypeHelper::matchType($value, [Stringable::class, 'object']),
            );
        }
    }

    /**
     * @depends testCheckTypeCase
     */
    public function testFilterBoolStrict()
    {
        static::assertTrue(DataTypeHelper::filterBool(true, true));
        static::assertNull(DataTypeHelper::filterBool('true', true));
        static::assertNull(DataTypeHelper::filterBool(1, true));

        static::assertFalse(DataTypeHelper::filterBool(false, true));
        static::assertNull(DataTypeHelper::filterBool('false', true));
        static::assertNull(DataTypeHelper::filterBool(0, true));
    }

    /**
     * @depends testCheckTypeCase
     */
    public function testFilterBoolConversion()
    {
        static::assertTrue(DataTypeHelper::filterBool(true, null));
        static::assertTrue(DataTypeHelper::filterBool('true', null));
        static::assertTrue(DataTypeHelper::filterBool(1, null));
        static::assertTrue(DataTypeHelper::filterBool('1', null));
        static::assertTrue(DataTypeHelper::filterBool([''], null));
        static::assertTrue(DataTypeHelper::filterBool([0], null));
        static::assertTrue(DataTypeHelper::filterBool([1], null));

        static::assertFalse(DataTypeHelper::filterBool('false', null));
        static::assertFalse(DataTypeHelper::filterBool('0', null));
        static::assertFalse(DataTypeHelper::filterBool('', null));
        static::assertFalse(DataTypeHelper::filterBool(0, null));
        static::assertFalse(DataTypeHelper::filterBool([], null));
        static::assertFalse(DataTypeHelper::filterBool(null, null));

        static::assertNull(DataTypeHelper::filterBool(new static(), null));
    }

    /**
     * @depends testCheckTypeCase
     */
    public function testFilterBoolDefault()
    {
        static::assertTrue(DataTypeHelper::filterBool(true, false));
        static::assertTrue(DataTypeHelper::filterBool(1, false));
        static::assertTrue(DataTypeHelper::filterBool('1', false));
        static::assertTrue(DataTypeHelper::filterBool('foo', false));
        static::assertTrue(DataTypeHelper::filterBool(['1'], false));
        static::assertTrue(DataTypeHelper::filterBool('false', false));

        static::assertFalse(DataTypeHelper::filterBool(false, false));
        static::assertFalse(DataTypeHelper::filterBool('0', false));
        static::assertFalse(DataTypeHelper::filterBool('', false));
        static::assertFalse(DataTypeHelper::filterBool(0, false));
        static::assertFalse(DataTypeHelper::filterBool([], false));
        static::assertFalse(DataTypeHelper::filterBool(null, false));
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

        $value = new class implements Stringable {
            public function __toString(): string
            {
                return 'foo';
            }
        };
        static::assertEquals('foo', DataTypeHelper::filterString($value));

        $value = new class {
            public function __toString(): string
            {
                return 'bar';
            }
        };
        static::assertEquals('bar', DataTypeHelper::filterString($value));

        static::assertNull(DataTypeHelper::filterString([]));
        static::assertNull(DataTypeHelper::filterString((object)[]));
    }

    public function testClassTypeCheckCase2()
    {
        $message = 'Argument $allowedTypes passed to humhub\helpers\DataTypeHelper::parseTypes must be one of string, string[], object[] - empty string given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::TYPE_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::TYPE_CHECK_VALUE_IS_EMPTY);

        DataTypeHelper::matchClassType(null, '');
    }

    public function testClassTypeCheckCase3()
    {
        $message = 'Argument $allowedTypes passed to humhub\helpers\DataTypeHelper::parseTypes must be one of string, string[], object[] - [] given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::TYPE_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::TYPE_CHECK_VALUE_IS_EMPTY);

        DataTypeHelper::matchClassType(null, []);
    }

    public function testClassTypeCheckCase4()
    {
        $message = 'Argument $allowedTypes passed to humhub\helpers\DataTypeHelper::parseTypes must be one of the following types: string, string[], object[], NULL - int given.';

        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::TYPE_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::TYPE_CHECK_INVALID_TYPE);

        DataTypeHelper::matchClassType(null, 0);
    }

    public function testClassTypeCheckCase5()
    {
        $message = 'Argument $allowedTypes[0] passed to humhub\helpers\DataTypeHelper::parseTypes must be a valid class/interface/trait name or an object instance - \'0\' given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::TYPE_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::TYPE_CHECK_NON_EXISTING_CLASS);

        DataTypeHelper::matchClassType(null, '0');
    }


    public function testClassTypeCheckCase6()
    {
        $message = 'Argument $allowedTypes[0] passed to humhub\helpers\DataTypeHelper::parseTypes must be a valid class/interface/trait name or an object instance - humhub\tests\codeception\unit\helpers\NonExistingClassName given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::TYPE_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::TYPE_CHECK_NON_EXISTING_CLASS);

        /** @noinspection PhpUndefinedClassInspection */
        DataTypeHelper::matchClassType(null, NonExistingClassName::class);
    }

    public function testClassTypeCheckCase7()
    {
        $message = 'Argument $allowedTypes[1] passed to humhub\helpers\DataTypeHelper::parseTypes must be a valid class/interface/trait name or an object instance - humhub\tests\codeception\unit\helpers\NonExistingClassName given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::TYPE_CHECK_INVALID_TYPE_PARAMETER + DataTypeHelper::TYPE_CHECK_NON_EXISTING_CLASS);

        /** @noinspection PhpUndefinedClassInspection */
        DataTypeHelper::matchClassType(null, [BaseObject::class, NonExistingClassName::class]);
    }

    public function testClassTypeCheckCaseNull()
    {
        static::assertNull(
            DataTypeHelper::matchClassType(null, BaseObject::class),
        );

        static::assertNull(
            DataTypeHelper::matchClassType(null, [BaseObject::class, null]),
        );

        $message = 'Argument $value passed to humhub\helpers\DataTypeHelper::matchClassType must be of type yii\base\BaseObject - NULL given.';

        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::TYPE_CHECK_INVALID_VALUE_PARAMETER + DataTypeHelper::TYPE_CHECK_VALUE_IS_EMPTY + DataTypeHelper::TYPE_CHECK_INVALID_TYPE + DataTypeHelper::TYPE_CHECK_VALUE_IS_NULL);

        DataTypeHelper::ensureClassType(null, BaseObject::class);
    }

    public function testClassTypeCheckCaseEmptyString()
    {
        static::assertNull(
            DataTypeHelper::matchClassType('', BaseObject::class),
        );

        $message = 'Argument $value passed to humhub\helpers\DataTypeHelper::matchClassType must be of type yii\base\BaseObject - empty string given.';

        $this->expectException(InvalidArgumentClassException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::TYPE_CHECK_INVALID_VALUE_PARAMETER + DataTypeHelper::TYPE_CHECK_VALUE_IS_EMPTY + DataTypeHelper::TYPE_CHECK_INVALID_TYPE + DataTypeHelper::TYPE_CHECK_TYPE_NOT_IN_LIST);

        DataTypeHelper::ensureClassType('', BaseObject::class);
    }

    public function testClassTypeCheckCaseString()
    {
        /** @noinspection PhpUndefinedClassInspection */
        static::assertNull(
            DataTypeHelper::matchClassType(NonExistingClassName::class, BaseObject::class, false),
        );

        $message = 'Argument $value passed to humhub\helpers\DataTypeHelper::matchClassType must be a valid class name or an object instance - humhub\tests\codeception\unit\helpers\NonExistingClassName given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::TYPE_CHECK_INVALID_VALUE_PARAMETER + DataTypeHelper::TYPE_CHECK_NON_EXISTING_CLASS);

        /** @noinspection PhpUndefinedClassInspection */
        DataTypeHelper::ensureClassType(NonExistingClassName::class, BaseObject::class);
    }

    public function testClassTypeCheckCaseWrongClass()
    {
        static::assertNull(
            DataTypeHelper::matchClassType(Exception::class, BaseObject::class),
        );

        $message = 'Argument $value passed to humhub\helpers\DataTypeHelper::matchClassType must be of type yii\base\BaseObject - PHPUnit\Framework\Exception given.';

        $this->expectException(InvalidArgumentClassException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::TYPE_CHECK_INVALID_VALUE_PARAMETER + DataTypeHelper::TYPE_CHECK_TYPE_NOT_IN_LIST);

        DataTypeHelper::ensureClassType(Exception::class, BaseObject::class);
    }

    public function testClassTypeCheckCaseWrongInstance()
    {
        static::assertNull(
            DataTypeHelper::matchClassType(new Exception('hello'), BaseObject::class),
        );

        $message = 'Argument $value passed to humhub\helpers\DataTypeHelper::matchClassType must be of type yii\base\BaseObject - PHPUnit\Framework\Exception given.';

        $this->expectException(InvalidArgumentClassException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::TYPE_CHECK_INVALID_VALUE_PARAMETER + DataTypeHelper::TYPE_CHECK_TYPE_NOT_IN_LIST + DataTypeHelper::TYPE_CHECK_VALUE_IS_INSTANCE);

        DataTypeHelper::ensureClassType(new Exception('hello'), BaseObject::class);
    }

    public function testClassTypeCheckCaseCorrectClass()
    {
        static::assertEquals(
            Exception::class,
            DataTypeHelper::matchClassType(Exception::class, Exception::class),
        );

        static::assertEquals(
            Exception::class,
            DataTypeHelper::matchClassType(Exception::class, [new \Exception()]),
        );

        static::assertEquals(
            BaseObject::class,
            DataTypeHelper::matchClassType(BaseObject::class, Configurable::class),
        );

        static::assertEquals(
            Model::class,
            DataTypeHelper::matchClassType(Model::class, ArrayableTrait::class),
        );

        static::assertNull(
            DataTypeHelper::matchClassType('#%' . Exception::class, Exception::class),
        );

        $message = 'Argument $value passed to humhub\helpers\DataTypeHelper::matchClassType must be a valid class name or an object instance - #%PHPUnit\Framework\Exception given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(DataTypeHelper::TYPE_CHECK_INVALID_VALUE_PARAMETER + DataTypeHelper::TYPE_CHECK_NON_EXISTING_CLASS);

        DataTypeHelper::ensureClassType('#%' . Exception::class, Exception::class);
    }

    public function testClassTypeCheckCaseCorrectInstance()
    {
        static::assertEquals(
            Exception::class,
            DataTypeHelper::matchClassType(new Exception('hello'), Exception::class),
        );

        static::assertEquals(
            Exception::class,
            DataTypeHelper::matchClassType(new Exception('hello'), [new \Exception()]),
        );

        static::assertEquals(
            BaseObject::class,
            DataTypeHelper::matchClassType(new BaseObject(), Configurable::class),
        );

        static::assertEquals(
            Model::class,
            DataTypeHelper::matchClassType(new Model(), ArrayableTrait::class),
        );
    }
}
