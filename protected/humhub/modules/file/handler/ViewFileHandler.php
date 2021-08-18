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
class ViewFileHandler extends BaseFileHandler
{

    /**
     * @inheritdoc
     */
    public $position = self::POSITION_TOP;

    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        return [
            'label' => Yii::t('FileModule.base', 'View'),
            'data-action-url' => Url::to(['/file/file/view', 'guid' => $this->file->guid]),
            'data-action-click' => 'ui.modal.load',
        ];
    }

}
