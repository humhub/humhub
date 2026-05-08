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
 * Allows uploading files of a specific type
 * @since 1.15
 */
class UploadFileHandler extends BaseFileHandler
{
    /**
     * @var string Available types: https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/accept
     */
    public $type = '*/*';

    /**
     * @inheritdoc
     */
    public $position = self::POSITION_TOP;

    /**
     * @var string
     */
    public $icon = 'cloud-upload';

    /**
     * @var string
     */
    public $label = '';

    public function getLabel(): string
    {
        return $this->label ?: Yii::t('FileModule.base', 'Attach a file');
    }

    /**
     * @inheritdoc
     */
    public function getLinkAttributes(): array
    {
        return [
            'label' => Icon::get($this->icon) . $this->getLabel(),
            'data-action-click' => 'file.uploadByType',
            'data-action-params' => '{"type":"' . $this->type . '"}',
        ];
    }
}
