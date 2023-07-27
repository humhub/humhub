<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\validators;

use yii\validators\FileValidator as YiiFileValidator;

/**
 * FileValidator
 *
 * @inheritdoc
 * @since 1.2
 */
class FileValidator extends YiiFileValidator
{
    use FileNameValidatorTrait;

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $this->validateFileName($model, $attribute);
        parent::validateAttribute($model, $attribute);
    }
}
