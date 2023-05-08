<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

use Yii;

/**
 * ImageFileHandler allows uploading images files type
 * @since 1.15
 */
class ImageFileHandler extends BaseFileHandler
{

    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        return [
            'label' => Yii::t('MailModule.base', 'Attach an image'),
            'data-action-click' => 'file.uploadByType',
            'data-action-params' => '{"type":"image/*"}', // Available types: https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/accept
        ];
    }

}
