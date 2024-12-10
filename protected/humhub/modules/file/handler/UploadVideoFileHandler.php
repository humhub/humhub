<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

use Yii;

/**
 * Allows uploading video files type
 * @since 1.15
 */
class UploadVideoFileHandler extends UploadFileHandler
{
    /**
     * @inerhitdoc
     */
    public $icon = 'video-camera';

    /**
     * @inerhitdoc
     */
    public $type = 'video/*';

    public function getLabel(): string
    {
        return Yii::t('FileModule.base', 'Attach a video');
    }
}
