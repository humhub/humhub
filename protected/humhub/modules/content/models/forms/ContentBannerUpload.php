<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\content\models\forms;

use humhub\modules\content\models\ContentBanner;
use humhub\modules\file\models\forms\FileUploadInterface;
use humhub\modules\file\models\forms\FileUploadTrait;
use Yii;

/**
 * UploadProfileImageForm allows uploads of profile images.
 *
 * Profile images will be used by spaces or users.
 *
 * @package humhub.forms
 * @since 0.5
 */
class ContentBannerUpload extends ContentBanner implements FileUploadInterface
{
    use FileUploadTrait;

    /**
     * Declares attribute labels.
     */
    public function attributeLabels(): array
    {
        return [
            static::$fileUploadFieldName => Yii::t('base', 'New banner image'),
        ];
    }
}
