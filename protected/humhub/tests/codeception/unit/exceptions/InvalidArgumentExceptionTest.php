<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\exceptions;

use Codeception\Test\Unit;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use yii\base\BaseObject;

/**
 * Class MimeHelperTest
 */
class InvalidArgumentExceptionTest extends Unit
{
    public function testInvalidArgumentValueExceptionMessageCase1()
    {
        $message = 'Hello World';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);

        throw new InvalidArgumentValueException($message);
    }

    public function testInvalidArgumentValueExceptionMessageCase2()
    {
        $message = 'Hello World';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(1);

        throw new InvalidArgumentValueException($message, 1);
    }

    public function testInvalidArgumentValueExceptionParameterCase1()
    {
        $message = 'Argument $parameter passed to ' . __METHOD__ . ' must be bool - 3 given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);

        throw new InvalidArgumentValueException('parameter', 'bool', 3);
    }

    public function testInvalidArgumentValueExceptionParameterCase2()
    {
        $message = 'Argument $parameter passed to ' . __METHOD__ . ' must be bool - NULL given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);

        throw new InvalidArgumentValueException('parameter', 'bool');
    }

    public function testInvalidArgumentValueExceptionParameterCase3()
    {
        $message = 'Argument $parameter passed to ' . __METHOD__ . ' must be one of bool, NULL - 2 given.';

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);

        throw new InvalidArgumentValueException('parameter', ['bool', null], 2);
    }

    public function testInvalidArgumentValueExceptionParameterCase4()
    {
        $message = 'Argument $valid passed to ' . __METHOD__ . ' must be one of the following types: string, string[] - NULL given.';

        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);

        throw new InvalidArgumentValueException('parameter');
    }

    public function testInvalidArgumentValueExceptionParameterCase5()
    {
        $message = 'Argument $valid[1] passed to ' . __METHOD__ . ' must be of type string - yii\base\BaseObject given.';

        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);

        throw new InvalidArgumentValueException('parameter', ['bool', new BaseObject()]);
    }
}
