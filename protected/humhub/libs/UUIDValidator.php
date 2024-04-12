<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use humhub\exceptions\InvalidConfigTypeException;
use Yii;
use yii\validators\StringValidator;

/**
 * UUID Generator Class provides static methods for creating or validating UUIDs.
 *
 * ----
 *
 * ## Configuration
 *
 * | case | `$allowNull` | `$strict` | `$autofillWith` | Input | Result |
 * |---|---|---|---|---|---|
 * | 1* | `true`* | `false`* | `true`* | `null`, `''`, `' '` | UUID |
 * | 2  | `true`* | `false`* | `false` | `null`, `''`, `' '` | `null` |
 * | 3  | `true`* | `false`* | `null`  | `null`, `''`, `' '` | `null` |
 * | | --- | --- | --- | --- | --- |
 * | 4 | `true`* | `true` | `true`* | `null`, `''`, `' '` | UUID |
 * | 5 | `true`* | `true` | `false` | `null`              | UUID |
 * | 6 | `true`* | `true` | `false` | `''`, `' '`         | `{attribute} cannot be blank.` |
 * | 7 | `true`* | `true` | `null`  | `null`, `''`, `' '` | `null` |
 * | | --- | --- | --- | --- | --- |
 * |   8 | `false` | not relevant | `true`* | `null`, `''`, `' '` | UUID |
 * |   9 | `false` | not relevant | `false` | `null`, `''`, `' '` | `{attribute} cannot be blank.` |
 * | n/a | `false` | not relevant | `null`  | not relevant        | config exception |
 * | | --- | --- | --- | --- | --- |
 * | 10 | not relevant | not relevant | `$UUID`      | `null`, `''`, `' '` | `$UUID` |
 * | 11 | not relevant | not relevant | `callable`   | `null`, `''`, `' '` | callable result |
 * | 12 | not relevant | not relevant | not relevant | `0`                 | `{attribute} must be a string (UUID) or null, int given.` |
 * | 12 | not relevant | not relevant | not relevant | `'0'`               | `{attribute} should contain at least 32 characters.` |
 * | 12 | not relevant | not relevant | not relevant | `false`             | `{attribute} must be a string (UUID) or null, bool given.` |
 * | 12 | not relevant | not relevant | not relevant | `[]`                | `{attribute} must be a string (UUID) or null, array given.` |
 *
 * ----
 *
 * ## Usage
 *
 * ```
 * // default settings: returning UUID on empty values
 * public function rules() {
 *     return [
 *          [['guid'], UUIDValidator::class],
 *     ];
 * }
 *
 *  // returning null on empty values
 *  public function rules() {
 *      return [
 *           [['guid'], UUIDValidator::class, ['autofillWith' => null,],
 *      ];
 *  }
 *  ```
 *
 * ----
 *
 * @since 1.15
 */
class UUIDValidator extends StringValidator
{
    public $min = UUID::UUID_LENGTH_MIN;
    public $max = UUID::UUID_LENGTH_MAX;

    /**
     * @var bool Specifies if NULL values are allowed
     * @see static::$autofillWith
     */
    protected bool $allowNull = true;

    public $strict = false;

    /**
     * @var string|bool|callable|null Unless false, this is considered If the model's $attribute value is null:
     *      If it's a valid UUID, it's value is used. If callable, its return value is used and validated.
     *      Otherwise, any non-empty value causes a new UUID being generated and used.
     * @see UUID::v4()
     * @see static::$allowNull
     */
    protected $autofillWith = true;
    public $skipOnEmpty = false;

    /**
     * @var string|null user-defined error message used when the value is blank
     */
    public ?string $messageOnBlank = null;

    /**
     * @var string|null user-defined error message used when the value is null, but null is not allowed
     */
    public ?string $messageOnForbiddenNull = null;

    /**
     * @var string|null user-defined error message used when the value is not an invalid UUID.
     */
    public ?string $messageOnInvalidUUID = null;

    /**
     * @var string|null user-defined error message used when the return value of the $autofillWith callback returns an invalid UUID
     */
    public ?string $messageOnInvalidCallbackResult = null;

    /**
     * @var bool|null Format valid UUIDs with dash (true), e.g., XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX,
     *              without dash (false) or unchanged (null)
     * @see UUID::validate()
     */
    public ?bool $formatWithDash = true;

    /**
     * @var bool|null Format valid UUIDs to lover-case (true), upper-case (false) or leave it unchanged (null)
     * @see UUID::validate()
     */
    public ?bool $formatToLowerCase = true;

    /**
     * @var bool|null Format valid UUIDs with enclosing curly brackets (true), e.g. {XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX},
     *              without (false), or unchanged (null)
     * @see UUID::validate()
     */
    public ?bool $formatWithCurlyBrackets = false;

    public function init()
    {
        if ($this->message === null) {
            $this->message = Yii::t('base', '{attribute} must be a string (UUID) or null; {type} given.');
        }

        if ($this->messageOnBlank === null) {
            $this->messageOnBlank = Yii::t('yii', '{attribute} cannot be blank.');
        }

        if ($this->messageOnForbiddenNull === null) {
            $this->messageOnBlank = Yii::t('yii', '{attribute} Null-values are not allowed.');
        }

        if ($this->messageOnInvalidUUID === null) {
            $this->messageOnInvalidUUID = Yii::t('base', '{attribute} must be an UUID or null. UUID has the format "{{XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX}}", where X = [a-fA-F0-9] and both curly brackets and delimiting dashes are optional.');
        }

        if ($this->messageOnInvalidCallbackResult === null) {
            $this->messageOnInvalidCallbackResult = Yii::t('base', '{attribute} has been empty. The system-configured function to fill empty values has returned an invalid value. Please try again or contact your administrator.');
        }

        parent::init();
    }

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        $result = $this->internalValidateValue($value, $changed, $model, $attribute);

        if ($result !== null) {
            $this->addError($model, $attribute, ...$result);

            return;
        }

        if ($changed) {
            $model->$attribute = $value;
        }
    }

    protected function validateValue($value): ?array
    {
        return $this->internalValidateValue($value);
    }

    protected function internalValidateValue(&$value, ?bool &$changed = false, $model = null, $attribute = null): ?array
    {
        $original = $value;
        $changed = false;

        if (is_string($value)) {
            $value = trim($value);

            if ($value === '' && !$this->strict) {
                $value = null;
            }
        }

        if ($value === null || $value === '') {
            switch (true) {
                case $this->autofillWith === false:
                    break;

                case $this->autofillWith === true:
                    $value = UUID::v4();
                    break;

                case is_callable($this->autofillWith):
                    $callable = &$this->autofillWith;
                    $value = $callable($value, $model, $attribute);

                    if ($value !== null) {
                        $value = UUID::validate(
                            $value,
                            $this->formatWithDash,
                            $this->formatToLowerCase,
                            $this->formatWithCurlyBrackets,
                        );

                        if ($value === null) {
                            // get the name of the callback function
                            is_callable($callable, true, $callable);

                            Yii::warning(sprintf(
                                "The callback function (%s) used to auto-fill an empty %s value returned an invalid result (%s).",
                                $callable,
                                $attribute,
                                StringHelper::toString($value, $type) ?? $type,
                            ));

                            return [$this->messageOnInvalidCallbackResult, []];
                        }
                    }
                    break;

                default:
                    $value = $this->autofillWith;
            }

            $changed = $original !== $value;

            if ($this->allowNull && $value === null) {
                return null;
            }

            if (empty($value)) {
                return [$this->messageOnBlank, []];
            }
        }

        $strict = $this->strict;
        $this->strict = true;

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = parent::validateValue($value);

        $this->strict = $strict;

        if ($result !== null) {
            $result[1]['type'] = get_debug_type($value);

            return $result;
        }

        $value = UUID::validate(
            $value,
            $this->formatWithDash,
            $this->formatToLowerCase,
            $this->formatWithCurlyBrackets,
        );

        $changed = $original !== $value;

        if ($value === null) {
            return [$this->messageOnInvalidUUID, []];
        }

        return null;
    }

    public function getAllowNull(): bool
    {
        return $this->allowNull;
    }

    /**
     * @throws InvalidConfigTypeException
     */
    public function setAllowNull(bool $allowNull): UUIDValidator
    {
        $this->allowNull = $allowNull;

        $this->validateConfig();

        return $this;
    }

    /**
     * @return bool|callable|string|null
     */
    public function getAutofillWith()
    {
        // if a UUID, make sure it is formatted according to the current settings
        return is_string($this->autofillWith)
            ? UUID::validate(
                $this->autofillWith,
                $this->formatWithDash,
                $this->formatToLowerCase,
                $this->formatWithCurlyBrackets,
            )
            : $this->autofillWith;
    }

    /**
     * @param bool|callable|string|null $autofillWith
     *
     * @return UUIDValidator
     * @throws InvalidConfigTypeException
     */
    public function setAutofillWith($autofillWith): UUIDValidator
    {
        if ($autofillWith !== null) {
            $bool = filter_var($autofillWith, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($bool !== null && $autofillWith !== '') {
                $autofillWith = $bool;
            } elseif (is_string($autofillWith)) {
                $uuid = UUID::validate($autofillWith, null, null, null);

                if ($uuid === null) {
                    throw new InvalidArgumentValueException('$autofillWith', ['a valid UUID'], $autofillWith);
                }

                $autofillWith = $uuid;
            } elseif (!is_callable($autofillWith)) {
                throw new InvalidArgumentTypeException(
                    '$autofillWith',
                    [null, 'bool', 'string', 'callable'],
                    $autofillWith,
                );
            }
        }

        $this->autofillWith = $autofillWith;

        $this->validateConfig();

        return $this;
    }

    public function setFormatToLowerCase(?bool $formatToLowerCase): UUIDValidator
    {
        $this->formatToLowerCase = $formatToLowerCase;
        return $this;
    }

    public function setFormatWithCurlyBrackets(?bool $formatWithCurlyBrackets): UUIDValidator
    {
        $this->formatWithCurlyBrackets = $formatWithCurlyBrackets;
        return $this;
    }

    public function setFormatWithDash(?bool $formatWithDash): UUIDValidator
    {
        $this->formatWithDash = $formatWithDash;
        return $this;
    }

    public function getStrict(): bool
    {
        return $this->strict;
    }

    public function setStrict(bool $strict): UUIDValidator
    {
        $this->strict = $strict;
        return $this;
    }

    /**
     * @throws InvalidConfigTypeException
     */
    protected function validateConfig()
    {
        if (!$this->allowNull && $this->autofillWith === null) {
            throw new InvalidConfigTypeException(sprintf('Invalid configuration for %s: $autofillWith may not be NULL, when $allowNull is true.', static::class));
        }
    }
}
