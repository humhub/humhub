<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace humhub\tests\codeception\unit\libs;

use Codeception\Test\Unit;
use humhub\exceptions\InvalidArgumentClassException;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use humhub\libs\Helpers;
use PHPUnit\Framework\Exception;
use yii\base\ArrayableTrait;
use yii\base\BaseObject;
use yii\base\Configurable;
use yii\base\Model;

/**
 * Class MimeHelperTest
 *
 * @noinspection IdentifierGrammar
 */
class HelpersTest extends Unit
{
    public function testClassTypeCheckCase1()
    {
        $message = 'Argument $type passed to humhub\libs\Helpers::checkClassType must be one of string, string[] - NULL given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_TYPE_PARAMETER + Helpers::CLASS_CHECK_VALUE_IS_EMPTY);

        Helpers::checkClassType(null, null);
    }

    public function testClassTypeCheckCase2()
    {
        $message = 'Argument $type passed to humhub\libs\Helpers::checkClassType must be one of string, string[] - empty string given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_TYPE_PARAMETER + Helpers::CLASS_CHECK_VALUE_IS_EMPTY);

        Helpers::checkClassType(null, '');
    }

    public function testClassTypeCheckCase3()
    {
        $message = 'Argument $type passed to humhub\libs\Helpers::checkClassType must be one of string, string[] - [] given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_TYPE_PARAMETER + Helpers::CLASS_CHECK_VALUE_IS_EMPTY);

        Helpers::checkClassType(null, []);
    }

    public function testClassTypeCheckCase4()
    {
        $message = 'Argument $type passed to humhub\libs\Helpers::checkClassType must be one of string, string[] - 0 given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_TYPE_PARAMETER + Helpers::CLASS_CHECK_VALUE_IS_EMPTY);

        Helpers::checkClassType(null, 0);
    }

    public function testClassTypeCheckCase5()
    {
        $message = 'Argument $type passed to humhub\libs\Helpers::checkClassType must be one of string, string[] - \'0\' given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_TYPE_PARAMETER + Helpers::CLASS_CHECK_VALUE_IS_EMPTY);

        Helpers::checkClassType(null, '0');
    }


    public function testClassTypeCheckCase6()
    {
        $message = 'Argument $type[0] passed to humhub\libs\Helpers::checkClassType must be a valid class/interface/trait name or an object instance - humhub\tests\codeception\unit\libs\NonExistingClassName given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_TYPE_PARAMETER + Helpers::CLASS_CHECK_NON_EXISTING_CLASS);

        /** @noinspection PhpUndefinedClassInspection */
        Helpers::checkClassType(null, NonExistingClassName::class);
    }

    public function testClassTypeCheckCase7()
    {
        $message = 'Argument $type[1] passed to humhub\libs\Helpers::checkClassType must be a valid class/interface/trait name or an object instance - humhub\tests\codeception\unit\libs\NonExistingClassName given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_TYPE_PARAMETER + Helpers::CLASS_CHECK_NON_EXISTING_CLASS);

        /** @noinspection PhpUndefinedClassInspection */
        Helpers::checkClassType(null, [BaseObject::class, NonExistingClassName::class]);
    }

    public function testClassTypeCheckCaseNull()
    {
        static::assertNull(
            Helpers::checkClassType(null, BaseObject::class, false)
        );

        static::assertNull(
            Helpers::checkClassType(null, [BaseObject::class, null])
        );

        $message = 'Argument $className passed to humhub\libs\Helpers::checkClassType must be of type yii\base\BaseObject - NULL given.';

        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + Helpers::CLASS_CHECK_VALUE_IS_EMPTY + Helpers::CLASS_CHECK_INVALID_TYPE + Helpers::CLASS_CHECK_VALUE_IS_NULL);

        Helpers::checkClassType(null, BaseObject::class);
    }

    public function testClassTypeCheckCaseEmptyString()
    {
        static::assertNull(
            Helpers::checkClassType('', BaseObject::class, false)
        );

        static::assertNull(
            Helpers::checkClassType('', [BaseObject::class, null], true, false)
        );

        $message = 'Argument $className passed to humhub\libs\Helpers::checkClassType must be of type yii\base\BaseObject - empty string given.';

        $this->expectException(InvalidArgumentClassException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + Helpers::CLASS_CHECK_VALUE_IS_EMPTY + Helpers::CLASS_CHECK_INVALID_TYPE + Helpers::CLASS_CHECK_TYPE_NOT_IN_LIST);

        Helpers::checkClassType('', BaseObject::class);
    }

    public function testClassTypeCheckCaseString()
    {
        /** @noinspection PhpUndefinedClassInspection */
        static::assertNull(
            Helpers::checkClassType(NonExistingClassName::class, BaseObject::class, false)
        );

        $message = 'Argument $className passed to humhub\libs\Helpers::checkClassType must be a valid class name or an object instance - humhub\tests\codeception\unit\libs\NonExistingClassName given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + Helpers::CLASS_CHECK_NON_EXISTING_CLASS);

        /** @noinspection PhpUndefinedClassInspection */
        Helpers::checkClassType(NonExistingClassName::class, BaseObject::class);
    }

    public function testClassTypeCheckCaseWrongClass()
    {
        static::assertNull(
            Helpers::checkClassType(Exception::class, BaseObject::class, false)
        );

        $message = 'Argument $className passed to humhub\libs\Helpers::checkClassType must be of type yii\base\BaseObject - PHPUnit\Framework\Exception given.';

        $this->expectException(InvalidArgumentClassException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + Helpers::CLASS_CHECK_TYPE_NOT_IN_LIST);

        Helpers::checkClassType(Exception::class, BaseObject::class);
    }

    public function testClassTypeCheckCaseWrongInstance()
    {
        static::assertNull(
            Helpers::checkClassType(new Exception('hello'), BaseObject::class, false)
        );

        $message = 'Argument $className passed to humhub\libs\Helpers::checkClassType must be of type yii\base\BaseObject - PHPUnit\Framework\Exception given.';

        $this->expectException(InvalidArgumentClassException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + Helpers::CLASS_CHECK_TYPE_NOT_IN_LIST + Helpers::CLASS_CHECK_VALUE_IS_INSTANCE);

        Helpers::checkClassType(new Exception('hello'), BaseObject::class);
    }

    public function testClassTypeCheckCaseCorrectClass()
    {
        static::assertEquals(
            Exception::class,
            Helpers::checkClassType(Exception::class, Exception::class)
        );

        static::assertEquals(
            Exception::class,
            Helpers::checkClassType(Exception::class, [new \Exception()])
        );

        static::assertEquals(
            BaseObject::class,
            Helpers::checkClassType(BaseObject::class, Configurable::class)
        );

        static::assertEquals(
            Model::class,
            Helpers::checkClassType(Model::class, ArrayableTrait::class)
        );

        static::assertEquals(
            Exception::class,
            Helpers::checkClassType('#%' . Exception::class, Exception::class, false, false)
        );

        static::assertNull(
            Helpers::checkClassType('#%' . Exception::class, Exception::class, false, true)
        );

        $message = 'Argument $className passed to humhub\libs\Helpers::checkClassType must be a valid class name or an object instance - #%PHPUnit\Framework\Exception given.';

        $this->expectException(InvalidArgumentClassException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(Helpers::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER);

        Helpers::checkClassType('#%' . Exception::class, Exception::class);
    }

    public function testClassTypeCheckCaseCorrectInstance()
    {
        static::assertEquals(
            Exception::class,
            Helpers::checkClassType(new Exception('hello'), Exception::class)
        );

        static::assertEquals(
            Exception::class,
            Helpers::checkClassType(new Exception('hello'), [new \Exception()])
        );

        static::assertEquals(
            BaseObject::class,
            Helpers::checkClassType(new BaseObject(), Configurable::class)
        );

        static::assertEquals(
            Model::class,
            Helpers::checkClassType(new Model(), ArrayableTrait::class)
        );
    }
}
