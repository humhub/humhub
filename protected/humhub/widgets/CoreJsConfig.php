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
            $userConfig['email'] = Yii::$app->user->getIdentity()->email;
        } else {
            $userConfig = ['isGuest' => true];
        }

        $userConfig['locale'] = Yii::$app->formatter->locale;

        $this->getView()->registerJsConfig(
                [
                    'user' => $userConfig,
                    'live' => [
                        'client' => Yii::$app->live->driver->getJsConfig()
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
                    'topic' => [
                        'icon' => '<i class="fa '.Yii::$app->getModule('topic')->icon.'"></i>'
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
                    'ui.richtext.prosemirror' => [
                        'emoji' => [
                            'twemoji' => [
                                'base' => Yii::getAlias('@web-static/img/twemoji/'),
                                'size' => '72x72',
                            ]
                        ],
                        'oembed' => [
                            'max' => Yii::$app->getModule('content')->maxOembeds
                        ],
                        'mention' => [
                            'minInput' => 2,
                            'minInputText' => Yii::t('base', 'Please type at least {count} characters', ['count' => 2])
                        ],
                        'text' => [
                            "Wrap in block quote" => Yii::t('ContentModule.richtexteditor', 'Wrap in block quote'),
                            "Wrap in bullet list" => Yii::t('ContentModule.richtexteditor', "Wrap in bullet list"),
                            "Toggle code font" => Yii::t('ContentModule.richtexteditor', "Toggle code font"),
                            "Change to code block" => Yii::t('ContentModule.richtexteditor', "Change to code block"),
                            "Code" => Yii::t('ContentModule.richtexteditor', "Code"),
                            "Toggle emphasis" => Yii::t('ContentModule.richtexteditor', "Toggle emphasis"),
                            "Change to heading" => Yii::t('ContentModule.richtexteditor', "Change to heading"),
                            "Insert horizontal rule" => Yii::t('ContentModule.richtexteditor', "Insert horizontal rule"),
                            "Horizontal rule" => Yii::t('ContentModule.richtexteditor', "Horizontal rule"),
                            "Insert image" => Yii::t('ContentModule.richtexteditor', "Insert image"),
                            "Image" => Yii::t('ContentModule.richtexteditor', "Image"),
                            "Location" => Yii::t('ContentModule.richtexteditor', "Location"),
                            "Title" => Yii::t('ContentModule.richtexteditor', "Title"),
                            "Width" => Yii::t('ContentModule.richtexteditor', "Width"),
                            "Height" => Yii::t('ContentModule.richtexteditor', "Height"),
                            "Add or remove link" => Yii::t('ContentModule.richtexteditor', "Add or remove link"),
                            "Create a link" => Yii::t('ContentModule.richtexteditor', "Create a link"),
                            "Link target" => Yii::t('ContentModule.richtexteditor', "Link target"),
                            "Wrap in ordered list" => Yii::t('ContentModule.richtexteditor', "Wrap in ordered list"),
                            "Change to paragraph" => Yii::t('ContentModule.richtexteditor', "Change to paragraph"),
                            "Paragraph" => Yii::t('ContentModule.richtexteditor', "Paragraph"),
                            "Toggle strikethrough" => Yii::t('ContentModule.richtexteditor', "Toggle strikethrough"),
                            "Toggle strong style" => Yii::t('ContentModule.richtexteditor', "Toggle strong style"),
                            "Create table" => Yii::t('ContentModule.richtexteditor', "Create table"),
                            "Delete table" => Yii::t('ContentModule.richtexteditor', "Delete table"),
                            "Insert table" => Yii::t('ContentModule.richtexteditor', "Insert table"),
                            "Rows" => Yii::t('ContentModule.richtexteditor', "Rows"),
                            "Columns" => Yii::t('ContentModule.richtexteditor', "Columns"),
                            "Insert column before" => Yii::t('ContentModule.richtexteditor', "Insert column before"),
                            "Insert column after" => Yii::t('ContentModule.richtexteditor', "Insert column after"),
                            "Delete column" => Yii::t('ContentModule.richtexteditor', "Delete column"),
                            "Insert row before" => Yii::t('ContentModule.richtexteditor', "Insert row before"),
                            "Insert row after" => Yii::t('ContentModule.richtexteditor', "Insert row after"),
                            "Delete row" => Yii::t('ContentModule.richtexteditor', "Delete row"),
                            "Upload and include a File" => Yii::t('ContentModule.richtexteditor', "Upload and include a File"),
                            "Upload File" => Yii::t('ContentModule.richtexteditor', "Upload File"),
                            "Insert" => Yii::t('ContentModule.richtexteditor', "Insert"),
                            "Type" => Yii::t('ContentModule.richtexteditor', "Type"),
                            "people" => Yii::t('ContentModule.richtexteditor', "People"),
                            "animals_and_nature" => Yii::t('ContentModule.richtexteditor', "Animals & Nature"),
                            "food_and_drink" => Yii::t('ContentModule.richtexteditor', "Food & Drink"),
                            "activity" => Yii::t('ContentModule.richtexteditor', "Activity"),
                            "travel_and_places" => Yii::t('ContentModule.richtexteditor', "Travel & Places"),
                            "objects" => Yii::t('ContentModule.richtexteditor', "Objects"),
                            "symbols" => Yii::t('ContentModule.richtexteditor', "Symbols"),
                            "flags" => Yii::t('ContentModule.richtexteditor', "Flags"),
                        ]
                    ],
                    'oembed' => [
                        'loadUrl' => Url::to(['/oembed'])
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
                    'ui.additions' => [
                        'text' => [
                            'success.clipboard' => Yii::t('base', 'Text has been copied to clipboard'),
                            'error.clipboard' => Yii::t('base', 'Text could not be copied to clipboard'),
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
                        'addImage' => $this->view->theme->getBaseUrl().'/img/picker_add.png',
                        'text' => [
                            'error.loadingResult' => Yii::t('base', 'An unexpected error occurred while loading the search result.'),
                            'showMore' => Yii::t('base', 'Show more'),
                            'addOption' => Yii::t('base', 'Add:'),
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
