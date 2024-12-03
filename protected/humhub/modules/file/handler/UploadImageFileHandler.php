<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

use Yii;

/**
 * Allows uploading images files type
 * @since 1.15
 */
class UploadImageFileHandler extends UploadFileHandler
{
    /**
     * @inerhitdoc
     */
    public $icon = 'camera';

    /**
     * @inerhitdoc
     */
    public $type = 'image/*';

    public function getLabel(): string
    {
        return Yii::t('FileModule.base', 'Attach an image');
    }
}
