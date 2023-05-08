<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

use Yii;

/**
 * AudioFileHandler allows uploading audio files type
 */
class AudioFileHandler extends BaseFileHandler
{

    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        return [
            'label' => Yii::t('MailModule.base', 'Attach an audio message'),
            'data-action-click' => 'file.uploadByType',
            'data-action-params' => '{"type":"audio/*"}', // Available types: https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/accept
        ];
    }

}
