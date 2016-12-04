<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Url;

/**
 * JSConfig LayoutAddition used to configure core js modules.
 *
 * @author buddha
 */
class CoreJsConfig extends Widget
{

    public function run()
    {
        $userConfig = ['isGuest' => Yii::$app->user->isGuest];
        if(!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
            $userConfig['guid'] = $user->displayName;
            $userConfig['displayName'] = \yii\helpers\Html::encode($user->displayName);
            $userConfig['image'] = $user->getProfileImage()->getUrl();
        }
        
        $this->getView()->registerJsConfig(
            [
                'user' => $userConfig,
                'file' => [
                    'upload' => [
                        'url' => Url::to(['/file/file/upload']),
                        'deleteUrl' => Url::to(['/file/file/delete'])
                    ],
                    'text' => [
                        'error.upload' => Yii::t('base', 'Some files could not be uploaded:'),
                        'success.delete' => Yii::t('base', 'The file has been deleted.'),
                    ]
                ],
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
                ],
                'ui.picker' => [
                    'text' => [
                        'error.loadingResult' => Yii::t('base', 'An unexpected error occured while loading the search result.'),
                        'showMore' => Yii::t('base', 'Show more'),
                    ]
                ],
                'content' => [
                    'text' => [
                        'modal.permalink.head' => Yii::t('ContentModule.widgets_views_permaLink', '<strong>Permalink</strong> to this post'),
                        'modal.permalink.info' => Yii::t('base', 'Copy to clipboard: Ctrl/Cmd+C'),
                        'modal.button.open' => Yii::t('base', 'Open'),
                        'modal.button.close' => Yii::t('base', 'Close'),
                    ]
                ],
                'comment' => [
                    'modal' => [
                        'delteConfirm' => [
                            'header' => Yii::t('CommentModule.widgets_views_showComment', '<strong>Confirm</strong> comment deleting'),
                            'body' => Yii::t('CommentModule.widgets_views_showComment', 'Do you really want to delete this comment?'),
                            'confirmText' => Yii::t('CommentModule.widgets_views_showComment', 'Delete'),
                            'cancelText'=> Yii::t('CommentModule.widgets_views_showComment', 'Cancel')
                        ]
                    ],
                    'text' => [
                        'success.delete' => Yii::t('CommentModule.widgets_views_showComment', 'Comment has been deleted')
                    ]
                ]
        ]);
    }

}
