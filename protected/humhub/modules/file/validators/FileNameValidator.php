<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\validators;

use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\validators\Validator;

/**
 * FileValidator
 *
 * @inheritdoc
 * @since 1.2
 */
class FileNameValidator extends Validator
{
    use FileNameValidatorTrait;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function validateAttribute($model, $attribute)
    {
        $this->validateFileName($model);
        $this->validateExtension($model);
    }
}
