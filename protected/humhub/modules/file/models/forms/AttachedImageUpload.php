<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\models\forms;

use humhub\modules\file\models\AttachedImage;
use Yii;

/**
 * UploadAttachedImageForm allows uploads of attached images.
 *
 * @param string $image
 *
 * @package humhub.forms
 * @since   1.15
 */
class AttachedImageUpload extends AttachedImage implements FileUploadInterface
{
    use FileUploadTrait;

    //  public properties

    /**
     * Declares attribute labels.
     */
    public function attributeLabels(): array
    {
        return [
            static::$fileUploadFieldName => Yii::t('base', 'New image'),
        ];
    }
}
