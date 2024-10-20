<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace unit\libs;

use Codeception\Util\Debug;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use humhub\exceptions\InvalidConfigTypeException;
use humhub\libs\UUID;
use humhub\libs\UUIDValidator;
use stdClass;
use Stringable;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\BaseObject;
use yii\base\Model;

/**
 * Class MimeHelperTest
 */
class UUIDTest extends HumHubDbTestCase
{
    protected const UUID = '12345678-AAAA-bbbb-cDeF-0123456789AB';

    /**
     * @var Stringable|object
     */
    private static Stringable $stringable;

    /**
     * @var object
     */
    private static object $toString;

    /**
     * @noinspection PhpHierarchyChecksInspection
     * @noinspection PhpUndefinedClassInspection
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$toString = new class {
            public string $uuid = '';

            public function __toString()
            {
                return $this->uuid;
            }
        };

        class_alias(get_class(static::$toString), __NAMESPACE__ . '\ToString');

        static::$stringable = new class extends ToString implements Stringable {
        };
    }

    public function testValidation()
    {
        static::assertNull(UUID::validate(null));
        static::assertNull(UUID::validate(1));
        static::assertNull(UUID::validate(1.2));
        static::assertNull(UUID::validate([]));
        static::assertNull(UUID::validate(new stdClass()));

        // test empty strings
        static::assertNull(UUID::validate(''));
        static::assertNull(UUID::validate(static::$toString));
        static::assertNull(UUID::validate(static::$stringable));
        static::assertNull(UUID::validate(' '));

        // test strings that are too short and too long
        static::assertNull(UUID::validate(str_repeat('1', 3)));
        static::assertNull(UUID::validate(str_repeat('1', 50)));

        static::assertEquals(self::UUID, UUID::validate(self::UUID, null, null, null));
        $variant = str_replace('-', '', self::UUID);
        static::assertEquals($variant, UUID::validate($variant, null, null, null));

        // test valid patterns without curly braces
        $this->runValidationTest(self::UUID, self::UUID);
        $this->runValidationTest(self::UUID, sprintf(' %s ', self::UUID));

        // test valid patterns with curly braces
        $this->runValidationTest(self::UUID, sprintf('{%s}', self::UUID));
        $this->runValidationTest(self::UUID, sprintf(' {%s} ', self::UUID));

        // test valid patterns with incoherent curly braces
        static::assertNull(UUID::validate(sprintf('%s}', self::UUID)));
        static::assertNull(UUID::validate(sprintf('{%s', self::UUID)));

        // test valid pattern with invalid delimiter
        static::assertNull(UUID::validate(str_replace('-', ' ', self::UUID)));
        static::assertNull(UUID::validate(str_replace('-', 'a', self::UUID)));
        static::assertNull(UUID::validate(str_replace('-', 'x', self::UUID)));

        // test missing dashes
        static::assertNull(UUID::validate('12345678AAAA-bbbb-cDeF-0123456789AB'));
        static::assertNull(UUID::validate('12345678-AAAAbbbb-cDeF-0123456789AB'));
        static::assertNull(UUID::validate('12345678-AAAA-bbbbcDeF-0123456789AB'));
        static::assertNull(UUID::validate('12345678-AAAA-bbbb-cDeF0123456789AB'));

        // test invalid block lengths
        static::assertNull(UUID::validate('123456789-AAAA-bbbb-cDeF-0123456789AB'));
        static::assertNull(UUID::validate('12345678-AAAAa-bbbb-cDeF-0123456789AB'));
        static::assertNull(UUID::validate('12345678-AAAA-bbbbB-cDeF-0123456789AB'));
        static::assertNull(UUID::validate('12345678-AAAA-bbbb-cDeF0-0123456789AB'));
        static::assertNull(UUID::validate('12345678-AAAA-bbbb-cDeF-0123456789ABC'));

        // test invalid character in each block
        static::assertNull(UUID::validate('1234567x-AAAA-bbbb-cDeF-0123456789AB'));
        static::assertNull(UUID::validate('12345678-AAAx-bbbb-cDeF-0123456789AB'));
        static::assertNull(UUID::validate('12345678-AAAA-bbbX-cDeF-0123456789AB'));
        static::assertNull(UUID::validate('12345678-AAAA-bbbb-cDeX-0123456789AB'));
        static::assertNull(UUID::validate('12345678-AAAA-bbbb-cDeF-0123456789AX'));
    }

    public function testFixtures()
    {
        $toCheck = [
            '@content/tests/codeception/fixtures/data/contentcontainer.php' => 'guid',
            '@space/tests/codeception/fixtures/data/space.php' => 'guid',
            '@user/tests/codeception/fixtures/data/user.php' => 'guid',
        ];

        foreach ($toCheck as $file => $column) {
            Yii::info(sprintf('Testing %s::%s', $file, $column), 'testFixtures');

            $file = Yii::getAlias($file);
            self::assertFileExists($file);

            $content = include($file);
            self::assertIsArray($content);

            $UUIDs = array_column($content, $column);
            self::assertNotEmpty($UUIDs);

            foreach ($UUIDs as $i => $UUID) {
                static::assertNotNull(
                    UUID::validate($UUID),
                    sprintf("Invalid UUID for row %s, column '%s' in '%s'", $i, $column, $file),
                );
            }
        }
    }

    public function testGenerator()
    {
        // test a few random versions
        static::assertNotNull(UUID::validate(UUID::v4()));
        static::assertNotNull(UUID::validate(UUID::v4()));
        static::assertNotNull(UUID::validate(UUID::v4()));
    }

    public function testUUIDValidatorConfig1()
    {
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator());

        /** @noinspection PhpUnhandledExceptionInspection */
        static::assertInstanceOf(UUIDValidator::class, Yii::createObject(UUIDValidator::class));
    }

    public function testUUIDValidatorConfigAutofill()
    {
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => true]));
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => false]));
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => 1]));
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => 0]));
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => 'true']));
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => 'false']));
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => static function () {
        }]));
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => [static::class, 'closureUUID']]));
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => [static::class, 'closureNull']]));
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => [static::class, 'closureInvalid']]));
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => UUID::v4()]));

        $validator = new UUIDValidator();

        /** @noinspection PhpUnhandledExceptionInspection */
        $validator->setAutofillWith(self::UUID);

        self::assertEquals(strtoupper(self::UUID), $validator->setFormatToLowerCase(false)->getAutofillWith());
        self::assertEquals(strtolower(self::UUID), $validator->setFormatToLowerCase(true)->getAutofillWith());
        self::assertEquals(self::UUID, $validator->setFormatToLowerCase(null)->getAutofillWith());

        /** not necessary to test all combinations, as thay have already been tested in @see static::testValidation() */
        self::assertEquals(str_replace('-', '', self::UUID), $validator->setFormatWithDash(false)->getAutofillWith());
        self::assertEquals('{' . self::UUID . '}', $validator->setFormatWithDash(null)->setFormatWithCurlyBrackets(true)->getAutofillWith());
    }

    public function testUUIDValidatorConfigAutofillNull()
    {
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => null]));
        static::assertInstanceOf(UUIDValidator::class, new UUIDValidator(['autofillWith' => null, 'allowNull' => true]));

        $this->expectException(InvalidConfigTypeException::class);
        $this->expectExceptionMessage('Invalid configuration for humhub\libs\UUIDValidator: $autofillWith may not be NULL, when $allowNull is true.');

        new UUIDValidator(['autofillWith' => null, 'allowNull' => false]);
    }

    public function testUUIDValidatorConfigAutofillEmptyString()
    {
        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage('Argument $autofillWith passed to humhub\libs\UUIDValidator::setAutofillWith must be a valid UUID - empty string given.');

        new UUIDValidator(['autofillWith' => '']);
    }

    public function testUUIDValidatorConfigAutofillInvalidString()
    {
        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage('Argument $autofillWith passed to humhub\libs\UUIDValidator::setAutofillWith must be a valid UUID - xxx given.');

        new UUIDValidator(['autofillWith' => 'xxx']);
    }

    public function testUUIDValidatorConfigAutofillNumber()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument $autofillWith passed to humhub\libs\UUIDValidator::setAutofillWith must be one of the following types: NULL, bool, string, callable - int given.');

        new UUIDValidator(['autofillWith' => 2]);
    }

    public function testUUIDValidatorConfigAutofillArray()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument $autofillWith passed to humhub\libs\UUIDValidator::setAutofillWith must be one of the following types: NULL, bool, string, callable - array given.');

        new UUIDValidator(['autofillWith' => []]);
    }

    public function testUUIDValidatorConfigAutofillObject()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument $autofillWith passed to humhub\libs\UUIDValidator::setAutofillWith must be one of the following types: NULL, bool, string, callable - yii\base\BaseObject given.');

        new UUIDValidator(['autofillWith' => new BaseObject()]);
    }

    public static function closureUUID(): ?string
    {
        return UUID::v4();
    }

    public static function closureNull(): ?string
    {
        return null;
    }

    public static function closureInvalid(): ?string
    {
        return 'xxx';
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testUUIDValidatorValidation()
    {
        $closureUUID = [static::class, 'closureUUID'];
        $closureNull = [static::class, 'closureNull'];
        $closureInvalid = [static::class, 'closureInvalid'];

        $validator = new UUIDValidator();

        $model = new class extends Model {
            /**
             * @var mixed
             */
            public $test;
        };

        $nullish = [null, '', '  '];
        $empty = [
            'Test must be a string (UUID) or null; int given.' => 0,
            'Test should contain at least 32 characters.' => '0',
            'Test must be a string (UUID) or null; bool given.' => false,
            'Test must be a string (UUID) or null; array given.' => [],
        ];

        /** @see UUIDValidator */
        $this->debugConfigCase(1, $validator->setAutofillWith(true));
        foreach ($nullish as $value) {
            $model->test = $value;
            $validator->validateAttribute($model, 'test');
            static::assertEmpty($model->getErrors());
            static::assertUUID($model->test);
        }

        /** @see UUIDValidator */
        $this->debugConfigCase(2, $validator->setAutofillWith(false));
        foreach ($nullish as $value) {
            $model->test = $value;
            $validator->validateAttribute($model, 'test');
            static::assertEmpty($model->getErrors());
            static::assertNull($model->test);
        }

        /** @see UUIDValidator */
        $this->debugConfigCase(3, $validator->setAutofillWith(null));
        foreach ($nullish as $value) {
            $model->test = $value;
            $validator->validateAttribute($model, 'test');
            static::assertEmpty($model->getErrors());
            static::assertNull($model->test);
        }

        $validator->strict = true;

        /** @see UUIDValidator */
        $this->debugConfigCase(7, $validator->setAutofillWith(true));
        foreach ($nullish as $value) {
            $model->test = $value;
            $validator->validateAttribute($model, 'test');
            static::assertEmpty($model->getErrors());
            static::assertUUID($model->test);
        }

        /** @see UUIDValidator */
        $this->debugConfigCase(5, $validator->setAutofillWith(false));
        $model->test = null;
        $validator->validateAttribute($model, 'test');
        static::assertEmpty($model->getErrors());
        static::assertNull($model->test);

        /** @see UUIDValidator */
        $this->debugConfigCase(6, $validator);
        foreach ($nullish as $value) {
            if ($value === null) {
                continue;
            }
            $model->test = $value;
            $validator->validateAttribute($model, 'test');
            static::assertError($model, 'test', 'Test Null-values are not allowed.');
        }

        /** @see UUIDValidator */
        $this->debugConfigCase(7, $validator->setAutofillWith(null));
        foreach ($nullish as $value) {
            $model->test = $value;
            $validator->validateAttribute($model, 'test');
            static::assertEmpty($model->getErrors());
            static::assertNull($model->test);
        }

        foreach ([false, true] as $strict) {
            $validator->strict = $strict;
        }

        $validator
            ->setStrict(false)
            ->setAutofillWith(true)
            ->setAllowNull(false)
        ;

        foreach ([false, true] as $strict) {
            $validator->strict = $strict;

            /** @see UUIDValidator */
            $this->debugConfigCase(8, $validator->setAutofillWith(true));
            foreach ($nullish as $value) {
                $model->test = $value;
                $validator->validateAttribute($model, 'test');
                static::assertEmpty($model->getErrors());
                static::assertUUID($model->test);
            }
        }

        foreach ([false, true] as $strict) {
            $validator->strict = $strict;

            /** @see UUIDValidator */
            $this->debugConfigCase(9, $validator->setAutofillWith(false));
            foreach ($nullish as $value) {
                $model->test = $value;
                $validator->validateAttribute($model, 'test');
                static::assertError($model, 'test', 'Test Null-values are not allowed.');
            }
        }

        foreach ([false, true] as $allowNull) {
            $validator->setAllowNull($allowNull);

            foreach ([false, true] as $strict) {
                $validator->strict = $strict;

                /** @see UUIDValidator */
                $this->debugConfigCase(10, $validator->setAutofillWith(self::UUID));
                foreach ($nullish as $value) {
                    $model->test = $value;
                    $validator->validateAttribute($model, 'test');
                    static::assertEmpty($model->getErrors());
                    static::assertEquals(strtolower(self::UUID), $model->test);
                }
            }
        }

        foreach ([false, true] as $allowNull) {
            $validator->setAllowNull($allowNull);

            foreach ([false, true] as $strict) {
                $validator->strict = $strict;

                /** @see UUIDValidator */
                $this->debugConfigCase(11, $validator->setAutofillWith($closureUUID));
                foreach ($nullish as $value) {
                    $model->test = $value;
                    $validator->validateAttribute($model, 'test');
                    static::assertEmpty($model->getErrors());
                    static::assertUUID($model->test);
                }

                $this->debugConfigCase(11, $validator->setAutofillWith($closureNull));
                foreach ($nullish as $value) {
                    $model->test = $value;
                    $validator->validateAttribute($model, 'test');

                    if ($allowNull) {
                        static::assertEmpty($model->getErrors());
                        static::assertNull($model->test);
                    } else {
                        static::assertError($model, 'test', $allowNull ? 'Test cannot be blank.' : 'Test Null-values are not allowed.');
                    }
                }

                // ToDo for v1.16
                // use logAssertions: 'The callback function (Closure::__invoke) used to auto-fill an empty test value returned an invalid result (NULL).'

                $this->debugConfigCase(11, $validator->setAutofillWith($closureInvalid));
                foreach ($nullish as $value) {
                    $model->test = $value;
                    $validator->validateAttribute($model, 'test');
                    static::assertError($model, 'test', 'Test has been empty. The system-configured function to fill empty values has returned an invalid value. Please try again or contact your administrator.');
                }
            }
        }

        foreach ([false, true] as $allowNull) {
            $validator->setAllowNull($allowNull);

            foreach ([false, true] as $strict) {
                $validator->strict = $strict;

                /**
                 * Test Configuration Case 101
                 *
                 * @see UUIDValidator
                 */
                foreach (
                    [
                        true,
                        false,
                        null,
                        self::UUID,
                        $closureNull,
                        $closureUUID,
                        $closureInvalid,
                    ] as $autoFill
                ) {
                    if (!$allowNull && $autoFill === null) {
                        continue;
                    }

                    /** @see UUIDValidator */
                    $this->debugConfigCase(12, $validator->setAutofillWith($autoFill));

                    foreach ($empty as $error => $value) {
                        $model->test = $value;
                        $validator->validateAttribute($model, 'test');
                        static::assertError($model, 'test', $error);
                    }
                }
            }
        }

        $uuid = UUID::v4();
        $model->test = null;
        $validator->setAutofillWith($uuid)
            ->validateAttribute($model, 'test');
        static::assertEmpty($model->getErrors());
        static::assertEquals($uuid, $model->test);

        $model->test = self::UUID;
        $validator->setAutofillWith(true)
            ->validateAttribute($model, 'test');
        static::assertEmpty($model->getErrors());
        static::assertEquals(strtolower(self::UUID), $model->test);
    }

    /**
     * @param string $normalized
     * @param mixed $input
     *
     * @return void
     * @noinspection PhpRedundantOptionalArgumentInspection
     */
    public function runValidationTest(string $normalized, $input): void
    {
        // remove curly brackets
        $expected1 = ltrim(rtrim($normalized, '}'), '{');

        static::assertEquals($expected1, UUID::validate($input, null, null, false));
        static::assertEquals(strtolower($expected1), UUID::validate($input, null, true, false));
        static::assertEquals(strtoupper($expected1), UUID::validate($input, null, false, false));

        // remove dashes
        $expected2 = str_replace('-', '', $expected1);

        static::assertEquals($expected2, UUID::validate($input, false, null, false));
        static::assertEquals(strtolower($expected2), UUID::validate($input, false, true, false));
        static::assertEquals(strtoupper($expected2), UUID::validate($input, false, false, false));

        // add curly brackets
        $expected1 = sprintf("{%s}", $expected1);

        static::assertEquals($expected1, UUID::validate($input, null, null, true));
        static::assertEquals(strtolower($expected1), UUID::validate($input, null, true, true));
        static::assertEquals(strtoupper($expected1), UUID::validate($input, null, false, true));

        // add curly brackets
        $expected2 = sprintf("{%s}", $expected2);

        static::assertEquals($expected2, UUID::validate($input, false, null, true));
        static::assertEquals(strtolower($expected2), UUID::validate($input, false, true, true));
        static::assertEquals(strtoupper($expected2), UUID::validate($input, false, false, true));
    }

    public function debugConfigCase(int $case, UUIDValidator $validator): void
    {
        $autofillWith = $validator->getAutofillWith();

        switch (true) {
            case $autofillWith === false:
                $autofillWith = 'false';
                break;

            case $autofillWith === true:
                $autofillWith = 'true';
                break;

            case $autofillWith === null:
                $autofillWith = 'NULL';
                break;

            case is_callable($autofillWith, true, $closure):
                $autofillWith = $closure;
                break;
        }

        /** @noinspection ForgottenDebugOutputInspection */
        Debug::debug(sprintf('Test Configuration Case %d: allowNull=%d; strict=%d; autofillWith=%s', $case, (int)$validator->getAllowNull(), (int)$validator->strict, $autofillWith));
    }

    /**
     * @param Model $model
     * @param $attribute
     *
     * @return void
     */
    public static function assertError(Model $model, string $attribute, ?string $error = null): void
    {
        $errors = $model->getErrors($attribute);

        if ($error) {
            static::assertContainsEquals($error, $errors, json_encode($errors, JSON_THROW_ON_ERROR));
        } else {
            static::assertNotEmpty($errors);
        }

        $model->clearErrors();
    }
}
