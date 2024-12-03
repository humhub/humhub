<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

use Yii;

/**
 * Allows uploading audio files type
 * @since 1.15
 */
class UploadAudioFileHandler extends UploadFileHandler
{
    /**
     * @inerhitdoc
     */
    public $icon = 'microphone';

    /**
     * @inerhitdoc
     */
    public $type = 'audio/*';

    public function getLabel(): string
    {
        return Yii::t('FileModule.base', 'Attach an audio message');
    }
}
