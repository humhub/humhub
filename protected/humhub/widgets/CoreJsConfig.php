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

        if (!Yii::$app->user->isGuest) {
            $userConfig = \humhub\modules\user\models\UserPicker::asJSON(Yii::$app->user->getIdentity());
            $userConfig['isGuest'] = false;
        } else {
            $userConfig = ['isGuest' => true];
        }

        $liveModule = Yii::$app->getModule('live');

        $this->getView()->registerJsConfig(
                [
                    'user' => $userConfig,
                    'live' => [
                        'client' => [
                            'type' => 'humhub.modules.live.poll.PollClient',
                            'options' => [
                                'url' => Url::to(['/live/poll']),
                                'initTime' => time(),
                                'minInterval' => $liveModule->minPollInterval, // Minimal polling request interval in seconds.
                                'maxInterval' => $liveModule->maxPollInterval, // Maximal polling request interval in seconds.
                                'idleFactor' => $liveModule->idleFactor, // Factor used in the actual interval calculation in case of user idle.
                                'idleInterval' => $liveModule->idleInterval //  Interval for updating the update delay in case of user idle in seconds.
                            ]
                        ]
                    ],
                    'client' => [
                      'baseUrl' =>  Yii::$app->settings->get('baseUrl')
                    ],
                    'file' => [
                        'upload' => [
                            'url' => Url::to(['/file/file/upload']),
                            'deleteUrl' => Url::to(['/file/file/delete'])
                        ],
                        'text' => [
                            'error.upload' => Yii::t('base', 'Some files could not be uploaded:'),
                            'error.unknown' => Yii::$app->user->isAdmin() ?
                                    Yii::t('base', 'An unknown error occurred while uploading. Hint: check your upload_max_filesize and post_max_size php settings.') : Yii::t('base', 'An unknown error occurred while uploading.'),
                            'success.delete' => Yii::t('base', 'The file has been deleted.')
                        ]
                    ],
                    'action' => [
                        'text' => [
                            'actionHandlerNotFound' => Yii::t('base', 'An error occurred while handling your last action. (Handler not found).'),
                        ]
                    ],
                    'ui.modal' => [
                        'defaultConfirmHeader' => Yii::t('base', '<strong>Confirm</strong> Action'),
                        'defaultConfirmBody' => Yii::t('base', 'Do you really want to perform this action?'),
                        'defaultConfirmText' => Yii::t('base', 'Confirm'),
                        'defaultCancelText' => Yii::t('base', 'Cancel')
                    ],
                    'ui.widget' => [
                        'text' => [
                            'error.unknown' => Yii::t('base', 'No error information given.'),
                            'info.title' => Yii::t('base', 'Info:'),
                            'error.title' => Yii::t('base', 'Error:')
                        ]
                    ],
                    'ui.richtext' => [
                        'emoji.url' => Yii::getAlias('@web-static/img/emoji/'),
                        'text' => [
                            'info.minInput' => Yii::t('base', 'Please type at least 3 characters'),
                            'info.loading' => Yii::t('base', 'Loading...'),
                        ]
                    ],
                    'ui.markdown', [
                        'text' => [
                            'Bold' => Yii::t('widgets_views_markdownEditor', 'Bold'),
                            'Italic' => Yii::t('widgets_views_markdownEditor', 'Italic'),
                            'Heading' => Yii::t('widgets_views_markdownEditor', 'Heading'),
                            'URL/Link' => Yii::t('widgets_views_markdownEditor', 'URL/Link'),
                            'Image/File' => Yii::t('widgets_views_markdownEditor', 'Image/File'),
                            'Image' => Yii::t('widgets_views_markdownEditor', 'Image'),
                            'List' => Yii::t('widgets_views_markdownEditor', 'List'),
                            'Preview' => Yii::t('widgets_views_markdownEditor', 'Preview'),
                            'strong text' => Yii::t('widgets_views_markdownEditor', 'strong text'),
                            'emphasized text' => Yii::t('widgets_views_markdownEditor', 'emphasized text'),
                            'heading text' => Yii::t('widgets_views_markdownEditor', 'heading text'),
                            'enter link description here' => Yii::t('widgets_views_markdownEditor', 'enter link description here'),
                            'Insert Hyperlink' => Yii::t('widgets_views_markdownEditor', 'Insert Hyperlink'),
                            'enter image description here' => Yii::t('widgets_views_markdownEditor', 'enter image description here'),
                            'Insert Image Hyperlink' => Yii::t('widgets_views_markdownEditor', 'Insert Image Hyperlink'),
                            'enter image title here' => Yii::t('widgets_views_markdownEditor', 'enter image title here'),
                            'list text here' => Yii::t('widgets_views_markdownEditor', 'list text here'),
                            'Quote' => Yii::t('widgets_views_markdownEditor', 'Quote'),
                            'quote here' => Yii::t('widgets_views_markdownEditor', 'quote here'),
                            'Code' => Yii::t('widgets_views_markdownEditor', 'Code'),
                            'code text here' => Yii::t('widgets_views_markdownEditor', 'code text here'),
                            'Unordered List' => Yii::t('widgets_views_markdownEditor', 'Unordered List'),
                            'Ordered List' => Yii::t('widgets_views_markdownEditor', 'Ordered List'),
                        ]
                    ],
                    'log' => [
                        'traceLevel' => (YII_DEBUG) ? 'DEBUG' : 'INFO',
                        'text' => [
                            'error.default' => Yii::t('base', 'An unexpected error occurred. If this keeps happening, please contact a site administrator.'),
                            'success.saved' => Yii::t('base', 'Saved'),
                            'saved' => Yii::t('base', 'Saved'),
                            'success.edit' => Yii::t('base', 'Saved'),
                            0 => Yii::t('base', 'An unexpected error occurred. If this keeps happening, please contact a site administrator.'),
                            403 => Yii::t('base', 'You are not allowed to run this action.'),
                            404 => Yii::t('base', 'The requested resource could not be found.'),
                            405 => Yii::t('base', 'Error while running your last action (Invalid request method).'),
                            500 => Yii::t('base', 'An unexpected server error occurred. If this keeps happening, please contact a site administrator.')
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
                            'error.loadingResult' => Yii::t('base', 'An unexpected error occurred while loading the search result.'),
                            'showMore' => Yii::t('base', 'Show more'),
                        ]
                    ],
                    'ui.showMore' => [
                        'text' => [
                            'readMore' => Yii::t('PostModule.widgets_views_post', 'Read full post...'),
                            'readLess' => Yii::t('PostModule.widgets_views_post', 'Collapse'),
                        ]
                    ],
                    'content' => [
                        'modal' => [
                            'permalink' => [
                                'head' => Yii::t('ContentModule.widgets_views_permaLink', '<strong>Permalink</strong> to this post'),
                                'info' => Yii::t('base', 'Copy to clipboard'),
                                'buttonOpen' => Yii::t('base', 'Open'),
                                'buttonClose' => Yii::t('base', 'Close'),
                            ],
                            'deleteConfirm' => [
                                'header' => Yii::t('ContentModule.widgets_views_deleteLink', '<strong>Confirm</strong> post deletion'),
                                'body' => Yii::t('ContentModule.widgets_views_deleteLink', 'Do you really want to delete this post? All likes and comments will be lost!'),
                                'confirmText' => Yii::t('ContentModule.widgets_views_deleteLink', 'Delete'),
                                'cancelText' => Yii::t('ContentModule.widgets_views_deleteLink', 'Cancel'),
                            ]
                        ],
                        'reloadUrl' => Url::to(['/content/content/reload']),
                        'deleteUrl' => Url::to(['/content/content/delete-id'])
                    ],
                    'stream' => [
                        'horizontalImageScrollOnMobile' => Yii::$app->settings->get('horImageScrollOnMobile'),
                        'defaultSort' => Yii::$app->getModule('stream')->settings->get('defaultSort', 'c'),
                        'text' => [
                            'success.archive' => Yii::t('ContentModule.widgets_views_stream', 'The content has been archived.'),
                            'success.unarchive' => Yii::t('ContentModule.widgets_views_stream', 'The content has been unarchived.'),
                            'success.pin' => Yii::t('ContentModule.widgets_views_stream', 'The content has been pinned.'),
                            'success.unpin' => Yii::t('ContentModule.widgets_views_stream', 'The content has been unpinned.'),
                            'success.delete' => Yii::t('ContentModule.widgets_views_stream', 'The content has been deleted.'),
                            'info.editCancel' => Yii::t('ContentModule.widgets_views_stream', 'Your last edit state has been saved!'),
                        ]
                    ],
                    'comment' => [
                        'modal' => [
                            'delteConfirm' => [
                                'header' => Yii::t('CommentModule.widgets_views_showComment', '<strong>Confirm</strong> comment deleting'),
                                'body' => Yii::t('CommentModule.widgets_views_showComment', 'Do you really want to delete this comment?'),
                                'confirmText' => Yii::t('CommentModule.widgets_views_showComment', 'Delete'),
                                'cancelText' => Yii::t('CommentModule.widgets_views_showComment', 'Cancel')
                            ]
                        ],
                        'text' => [
                            'success.delete' => Yii::t('CommentModule.widgets_views_showComment', 'Comment has been deleted')
                        ]
                    ],
                    'space' => [
                        'text' => [
                            'success.archived' => Yii::t('base', 'The space has been archived.'),
                            'success.unarchived' => Yii::t('base', 'The space has been unarchived.'),
                        ]
                    ],
        ]);
    }

}
