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
 * VideoFileHandler allows uploading video files type
 * @since 1.15
 */
class VideoFileHandler extends BaseFileHandler
{

    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        return [
            'label' => Icon::get('video-camera') . Yii::t('FileModule.base', 'Attach a video'),
            'data-action-click' => 'file.uploadByType',
            'data-action-params' => '{"type":"video/*"}', // Available types: https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/accept
        ];
    }

}
