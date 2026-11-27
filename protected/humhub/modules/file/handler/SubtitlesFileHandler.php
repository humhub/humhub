<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

use humhub\widgets\Icon;
use Yii;
use yii\helpers\Url;

/**
 * Opens the subtitles manager of an audio or video file
 *
 * @since 1.20
 */
class SubtitlesFileHandler extends BaseFileHandler
{
    /**
     * @var string
     */
    public $icon = 'badge-cc';

    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        return [
            'label' => Icon::get($this->icon) . Yii::t('FileModule.base', 'Subtitles'),
            'data-action-url' => Url::to(['/file/subtitles', 'guid' => $this->file->guid]),
            'data-action-click' => 'ui.modal.load',
            'data-modal-id' => 'subtitles-modal',
            'data-modal-close' => '',
        ];
    }
}
