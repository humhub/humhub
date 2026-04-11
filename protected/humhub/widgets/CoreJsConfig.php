<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\modules\admin\libs\CacheHelper;
use humhub\modules\file\validators\FileValidator;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\models\User;
use humhub\modules\user\models\UserPicker;
use humhub\modules\web\security\helpers\Security;
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
            $userConfig = UserPicker::asJSON(Yii::$app->user->getIdentity());
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
                    'client' => Yii::$app->live->driver->getJsConfig(),
                    'isActive' => Yii::$app->getModule('live')->isActive && !Yii::$app->user->isGuest,
                ],
                'client' => [
                    'baseUrl' => Yii::$app->settings->get('baseUrl'),
                    'reloadableScripts' => CacheHelper::getReloadableScriptUrls(),
                    'cspViolationReloadInterval' => Security::CSP_VIOLATION_RELOAD_INTERVAL,
                ],
                'i18n' => [
                    'revision' => Yii::$app->systemRevision->getPublicSignature(),
                    'language' => Yii::$app->language,
                    'translationUrl' => Url::to(['/i18n/translations']),
                    'version' => Yii::$app->version,
                ],
                'file' => [
                    'upload' => [
                        'url' => Url::to(['/file/file/upload']),
                        'deleteUrl' => Url::to(['/file/file/delete']),
                    ],
                    'url' => [
                        'download' => Url::to(['/file/file/download', 'download' => true, 'guid' => '-guid-'], true),
                        'load' => Url::to(['/file/file/download', 'guid' => '-guid-'], true),
                        'view' => Url::to(['/file/view', 'guid' => '-guid-'], true),
                    ],
                    'text' => [
                        'error.unknown' => Yii::t('base', 'An unexpected error occurred. Please check whether your file exceeds the allowed upload limit of {maxUploadSize}.', ['maxUploadSize' => Yii::$app->formatter->asShortSize((new FileValidator())->getSizeLimit())]) . (Yii::$app->user->isAdmin()
                                ? '(' . Yii::t('base', 'verify your upload_max_filesize and post_max_size php settings.') . ')' : ''),
                    ],
                ],
                'topic' => [
                    'icon' => '<i class="fa ' . Yii::$app->getModule('topic')->icon . '"></i>',
                ],
                'ui.richtext' => [
                    'emoji.url' => Yii::getAlias('@web-static/img/emoji/'),
                    'text' => [
                        'info.minInput' => Yii::t('base', 'Please type at least 3 characters'),
                        'info.loading' => Yii::t('base', 'Loading...'),
                    ],
                ],
                'ui.richtext.prosemirror' => [
                    'emoji' => [
                        'twemoji' => [
                            'base' => Yii::getAlias(Yii::$app->params['twemoji']['path']),
                            'size' => Yii::getAlias(Yii::$app->params['twemoji']['size']),
                        ],
                    ],
                    'oembed' => [
                        'max' => Yii::$app->getModule('content')->maxOembeds,
                    ],
                    'markdownEditorMode' => Yii::$app->user->getIdentity()
                        ? Yii::$app->user->getIdentity()->settings->get('markdownEditorMode')
                        : User::EDITOR_RICH_TEXT,
                    'mention' => [
                        'minInput' => 0,
                    ],
                ],
                'oembed' => [
                    'loadUrl' => Url::to(['/oembed']),
                    'displayUrl' => Url::to(['/oembed/display']),
                    'text' => [
                        'brokenUrl' => Yii::t('base', 'The URLs cannot be embedded: {urls}'),
                    ],
                ],
                'ui.markdown', [
                    'text' => [
                        'Bold' => Yii::t('UiModule.markdownEditor', 'Bold'),
                        'Italic' => Yii::t('UiModule.markdownEditor', 'Italic'),
                        'Heading' => Yii::t('UiModule.markdownEditor', 'Heading'),
                        'URL/Link' => Yii::t('UiModule.markdownEditor', 'URL/Link'),
                        'Image/File' => Yii::t('UiModule.markdownEditor', 'Image/File'),
                        'Image' => Yii::t('UiModule.markdownEditor', 'Image'),
                        'List' => Yii::t('UiModule.markdownEditor', 'List'),
                        'Preview' => Yii::t('UiModule.markdownEditor', 'Preview'),
                        'strong text' => Yii::t('UiModule.markdownEditor', 'strong text'),
                        'emphasized text' => Yii::t('UiModule.markdownEditor', 'emphasized text'),
                        'heading text' => Yii::t('UiModule.markdownEditor', 'heading text'),
                        'enter link description here' => Yii::t('UiModule.markdownEditor', 'enter link description here'),
                        'Insert Hyperlink' => Yii::t('UiModule.markdownEditor', 'Insert Hyperlink'),
                        'enter image description here' => Yii::t('UiModule.markdownEditor', 'enter image description here'),
                        'Insert Image Hyperlink' => Yii::t('UiModule.markdownEditor', 'Insert Image Hyperlink'),
                        'enter image title here' => Yii::t('UiModule.markdownEditor', 'enter image title here'),
                        'list text here' => Yii::t('UiModule.markdownEditor', 'list text here'),
                        'Quote' => Yii::t('UiModule.markdownEditor', 'Quote'),
                        'quote here' => Yii::t('UiModule.markdownEditor', 'quote here'),
                        'Code' => Yii::t('UiModule.markdownEditor', 'Code'),
                        'code text here' => Yii::t('UiModule.markdownEditor', 'code text here'),
                        'Unordered List' => Yii::t('UiModule.markdownEditor', 'Unordered List'),
                        'Ordered List' => Yii::t('UiModule.markdownEditor', 'Ordered List'),
                    ],
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
                        500 => Yii::t('base', 'An unexpected server error occurred. If this keeps happening, please contact a site administrator.'),
                    ],
                ],
                'ui.status' => [
                    'showMore' => Yii::$app->user->isAdmin() || YII_DEBUG,
                    'text' => [
                        'showMore' => Yii::t('base', 'Show more'),
                        'showLess' => Yii::t('base', 'Show less'),
                    ],
                ],
                'ui.picker' => [
                    'addImage' => $this->view->theme->getBaseUrl() . '/img/picker_add.png',
                ],
                'ui.panel' => [
                    'icon' => [
                        'up' => Icon::get('minus-square')->asString(),
                        'down' => Icon::get('plus-square')->asString(),
                    ],
                ],
                'content' => [
                    'modal' => [
                        'permalink' => [
                            'head' => Yii::t('ContentModule.base', '<strong>Permalink</strong> to this post'),
                            'info' => Yii::t('base', 'Copy to clipboard'),
                            'buttonOpen' => Yii::t('base', 'Open'),
                            'buttonClose' => Yii::t('base', 'Close'),
                        ],
                        'deleteConfirm' => [
                            'header' => Yii::t('ContentModule.base', '<strong>Delete</strong> content?'),
                            'body' => Yii::t('ContentModule.base', 'Do you want to delete this content, including all comments and attachments?<br><br>Please note: If a stream entry was created using a module, the original content that this entry is linked to will also be deleted.'),
                            'confirmText' => Yii::t('ContentModule.base', 'Delete'),
                            'cancelText' => Yii::t('ContentModule.base', 'Cancel'),
                        ],
                    ],
                    'reloadUrl' => Url::to(['/content/content/reload']),
                    'deleteUrl' => Url::to(['/content/content/delete-id']),
                    'adminDeleteModalUrl' => Url::to(['/content/content/get-admin-delete-modal']),
                ],
                'stream' => [
                    'defaultSort' => Yii::$app->getModule('stream')->settings->get('defaultSort', 'c'),
                ],
            ],
        );
    }
}
