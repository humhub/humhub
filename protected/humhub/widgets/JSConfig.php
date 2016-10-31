<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use yii\base\Widget;
use Yii;

/**
 * AjaxButton is an replacement for Yii1 CHtml::AjaxButton
 *
 * @author luke
 */
class JSConfig extends Widget
{

    public function run()
    {
        $this->getView()->registerJsConfig(
            [
                'action' => [
                    'text' => [
                        'actionHandlerNotFound' => Yii::t('base', 'An error occured while handling your last action. (Handler not found).'),
                    ]
                ],
                'ui.modal' => [
                    'defaultConfirmHeader' => Yii::t('base', '<strong>Confirm</strong> Action'),
                    'defaultConfirmBody' => Yii::t('base', 'Do you really want to perform this action?'),
                    'defaultConfirmText' => Yii::t('base', 'Confirm'),
                    'defaultCancelText' => Yii::t('base', 'Cancel')
                ],
                'log' => [
                    'traceLevel' => (YII_DEBUG) ? 'DEBUG' : 'INFO',
                    'text' => [
                        'error.default' => Yii::t('base', 'An unexpected error occured. If this keeps happening, please contact a site administrator.'),
                        'success.saved' => Yii::t('base', 'Saved'),
                        'success.edit' => Yii::t('base', 'Saved'),
                        0 => Yii::t('base', 'An unexpected error occured. If this keeps happening, please contact a site administrator.'),
                        403 => Yii::t('base', 'You are not allowed to run this action.'),
                        405 => Yii::t('base', 'Error while running your last action (Invalid request method).'),
                        500 => Yii::t('base', 'An unexpected server error occured. If this keeps happening, please contact a site administrator.')
                    ]
                ],
                'ui.status' => [
                    'showMore' => Yii::$app->user->isAdmin() || YII_DEBUG,
                    'text' => [
                        'showMore' => Yii::t('base', 'Show more'),
                        'showLess' => Yii::t('base', 'Show less')
                    ]
                ]
        ]);
    }

}
