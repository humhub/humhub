<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\modal;

use Yii;

/**
 * GlobalConfirmModal used as template for humhub.ui.modal.confirm actions.
 *
 * @see LayoutAddons
 * @author buddha
 * @since 1.2
 */
class GlobalConfirmModal extends \yii\base\Widget
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        return \humhub\widgets\modal\JsModal::widget([
            'id' => 'globalModalConfirm',
            'title' => ' ', // Force creation of the title element
            'jsWidget' => 'ui.modal.ConfirmModal',
            'backdrop' => false,
            'keyboard' => false,
            'initialLoader' => false,
            'footer' => '<button data-modal-cancel data-modal-close class="btn btn-light">' . Yii::t('base', 'Cancel') . '</button><button data-modal-confirm data-modal-close class="btn btn-primary">' . Yii::t('base', 'Confirm') . '</button>',
        ]);
    }

}
