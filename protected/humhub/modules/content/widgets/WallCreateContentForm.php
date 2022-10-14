<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\libs\Sort;
use humhub\modules\content\permissions\CreatePublicContent;
use humhub\modules\content\widgets\stream\WallStreamEntryWidget;
use humhub\modules\stream\actions\StreamEntryResponse;
use humhub\modules\topic\models\Topic;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\components\Widget;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\content\models\Content;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;
use Yii;
use yii\web\HttpException;

/**
 * WallCreateContentForm is the base widget to create  "quick" create content forms above Stream/Wall.
 *
 * @author luke
 */
class WallCreateContentForm extends Widget
{

    /**
     * @var string form submit route/url (required)
     */
    public $submitUrl;

    /**
     * @var ContentContainerActiveRecord this content will belong to
     */
    public $contentContainer;

    /**
     * @var bool Display menu above this form in order to select a content type like Post, Poll, Task and etc.
     */
    public $displayContentTabs = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!($this->contentContainer instanceof ContentContainerActiveRecord)) {
            throw new HttpException(500, 'No Content Container given!');
        }

        parent::init();
    }

    /**
     * Returns the custom form implementation.
     *
     * @return string
     */
    public function renderForm()
    {
        return '';
    }

    /**
     * Returns the custom form implementation.
     *
     * @param ActiveForm $form
     * @return string
     */
    public function renderActiveForm(ActiveForm $form): string
    {
        return $this->renderForm();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->contentContainer->visibility !== Space::VISIBILITY_NONE && $this->contentContainer->can(CreatePublicContent::class)) {
            $defaultVisibility = $this->contentContainer->getDefaultContentVisibility();
        } else {
            $defaultVisibility = Content::VISIBILITY_PRIVATE;
        }

        return $this->render('@humhub/modules/content/widgets/views/wallCreateContentForm', [
            'wallCreateContentForm' => $this,
            'contentContainer' => $this->contentContainer,
            'defaultVisibility' => $defaultVisibility,
        ]);
    }

    /**
     * Creates the given ContentActiveRecord based on given submitted form information.
     *
     * - Automatically assigns ContentContainer
     * - Access Check
     * - User Notification / File Uploads
     * - Reloads Wall after successfull creation or returns error json
     *
     * [See guide section](guide:dev-module-stream.md#CreateContentForm)
     *
     * @param ContentActiveRecord $record
     * @return array json
     */
    public static function create(ContentActiveRecord $record, ContentContainerActiveRecord $contentContainer = null)
    {
        Yii::$app->response->format = 'json';

        $visibility = Yii::$app->request->post('visibility', Content::VISIBILITY_PRIVATE);
        if ($visibility == Content::VISIBILITY_PUBLIC && !$contentContainer->can(CreatePublicContent::class)) {
            $visibility = Content::VISIBILITY_PRIVATE;
        }

        $record->content->visibility = $visibility;
        $record->content->container = $contentContainer;

        // Handle Notify User Features of ContentFormWidget
        // ToDo: Check permissions of user guids
        $userGuids = Yii::$app->request->post('notifyUserInput');
        if (!empty($userGuids)) {
            foreach ($userGuids as $guid) {
                $user = User::findOne(['guid' => trim($guid)]);
                if ($user) {
                    $record->content->notifyUsersOfNewContent[] = $user;
                }
            }
        }

        if ($record->save()) {
            $topics = Yii::$app->request->post('postTopicInput');
            if(!empty($topics)) {
                Topic::attach($record->content, $topics);
            }

            $record->fileManager->attach(Yii::$app->request->post('fileList'));
            return StreamEntryResponse::getAsArray($record->content);
        }

        return ['errors' => $record->getErrors()];
    }

    /**
     * @inheritdoc
     */
    public static function widget($config = [])
    {
        return get_called_class() === WallCreateContentForm::class
            ? self::renderTopSortedForm($config)
            : parent::widget($config);
    }

    /**
     * Render top sorted Form
     *
     * @param array $config
     * @return string
     * @throws \Exception
     */
    public static function renderTopSortedForm($config = [])
    {
        if (empty($config['contentContainer']) || !($config['contentContainer'] instanceof ContentContainerActiveRecord)) {
            return parent::widget($config);
        }

        $forms = [];
        foreach (Yii::$app->moduleManager->getContentClasses($config['contentContainer']) as $contentClass) {
            $content = new $contentClass($config['contentContainer']);
            if (!($content instanceof ContentActiveRecord)) {
                continue;
            }

            $wallEntryWidget = WallStreamEntryWidget::getByContent($content);
            if (!$wallEntryWidget) {
                continue;
            }

            if (!$wallEntryWidget->hasCreateForm()) {
                continue;
            }

            $forms[] = [
                'class' => $wallEntryWidget->createFormClass,
                'sortOrder' => $wallEntryWidget->createFormSortOrder ?? '9999999-' . $content->getContentName(),
            ];
        }

        if (empty($forms)) {
            return parent::widget($config);
        }

        Sort::sort($forms);

        if (!isset($config['displayContentTabs'])) {
            $config['displayContentTabs'] = true;
        }

        foreach ($forms as $form) {
            return $form['class']::widget($config);
        }
    }

}
