<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

use humhub\modules\ui\icon\widgets\Icon;
use Yii;

/**
 * AudioFileHandler allows uploading audio files type
 * @since 1.15
 */
class AudioFileHandler extends BaseFileHandler
{

    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        return [
            'label' => Icon::get('microphone') . Yii::t('FileModule.base', 'Attach an audio message'),
            'data-action-click' => 'file.uploadByType',
            'data-action-params' => '{"type":"audio/*"}', // Available types: https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/accept
        ];
    }

}
