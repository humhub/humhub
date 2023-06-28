<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\models\forms;

use humhub\modules\file\models\AttachedFile;
use Yii;

/**
 * UploadAttachedFile allows uploads of ActiveRecord attached images.
 *
 * @package humhub.forms
 * @since   1.15
 *
 * @property-read null|string $fieldNameFromController
 */
class AttachedFileUpload
    extends AttachedFile
    implements FileUploadInterface
{
    use FileUploadTrait;

    /**
     * Declares attribute labels.
     */
    public function attributeLabels(): array
    {
        return [
            static::$fileUploadFieldName => Yii::t('base', 'New file'),
        ];
    }

}
