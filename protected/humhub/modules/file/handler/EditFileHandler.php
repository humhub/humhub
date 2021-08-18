<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

use Yii;
use yii\helpers\Url;

/**
 * ViewFileHandler provides the open link for a file
 *
 * @since 1.10
 * @author Luke
 */
class EditFileHandler extends BaseFileHandler
{

    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        return [
            'label' => Yii::t('FileModule.base', 'Edit'),
            'data-action-url' => Url::to(['/file/file/edit', 'guid' => $this->file->guid]),
            'data-action-click' => 'ui.modal.load',
        ];
    }

}
