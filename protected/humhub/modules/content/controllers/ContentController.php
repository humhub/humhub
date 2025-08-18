<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers;

use humhub\components\behaviors\AccessControl;
use humhub\components\Controller;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\forms\AdminDeleteContentForm;
use humhub\modules\content\models\forms\ScheduleOptionsForm;
use humhub\modules\content\Module;
use humhub\modules\content\permissions\CreatePublicContent;
use humhub\modules\content\widgets\AdminDeleteModal;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\stream\actions\StreamEntryResponse;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\IntegrityException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ContentController is responsible for basic content objects.
 *
 * @package humhub.modules_core.wall.controllers
 * @since 0.5
 * @author Luke
 */
class ContentController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
            ],
        ];
    }

    /**
     * Deletes a content object
     *
     * Returns a JSON list of affected wallEntryIds.
     */
    public function actionDelete()
    {
        $this->forcePostRequest();

        $model = Yii::$app->request->get('model');

        // Due to backward compatibility we use the old delete mechanism in case a model parameter is provided
        $id = $model ? Yii::$app->request->get('id') : Yii::$app->request->post('id');

        /* @var $contentObj Content */
        $contentObj = $model ? Content::Get($model, $id) : Content::findOne(['id' => $id]);

        if (!$contentObj) {
            throw new NotFoundHttpException();
        }

        if (!$contentObj->canEdit()) {
            throw new ForbiddenHttpException();
        }

        $form = new AdminDeleteContentForm(['content' => $contentObj]);
        $form->load(Yii::$app->request->post());

        if (!$form->delete()) {
            return $this->asJson(['error' => $form->getErrorsAsString()]);
        }

        return $this->asJson([
            'success' => true,
            'uniqueId' => $contentObj->getUniqueId(),
            'model' => $model,
            'pk' => $id,
        ]);
    }

    /**
     * Returns modal content for admin to delete content
     */
    public function actionGetAdminDeleteModal()
    {
        Yii::$app->response->format = 'json';

        $id = Yii::$app->request->post('id');

        $contentObj = Content::findOne(['id' => $id]);

        if (!$contentObj) {
            throw new NotFoundHttpException();
        }

        if (!$contentObj->canEdit()) {
            throw new ForbiddenHttpException();
        }

        return [
            'header' => Yii::t('ContentModule.base', '<strong>Delete</strong> content?'),
            'body' => AdminDeleteModal::widget([
                'model' => new AdminDeleteContentForm(),
            ]),
            'confirmText' => Yii::t('ContentModule.base', 'Confirm'),
            'cancelText' => Yii::t('ContentModule.base', 'Cancel'),
        ];
    }

    /**
     * Archives an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionArchive()
    {
        $this->forcePostRequest();

        $content = Content::findOne(Yii::$app->request->get('id'));

        $result = $content instanceof Content
            && $content->canArchive()
            && $content->archive();

        return $this->asJson(['success' => $result]);
    }

    /**
     * UnArchives an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionUnarchive()
    {
        $this->forcePostRequest();

        $content = Content::findOne(Yii::$app->request->get('id'));

        $result = $content instanceof Content
            && $content->canArchive()
            && $content->unarchive();

        return $this->asJson(['success' => $result]);
    }

    public function actionDeleteId()
    {
        $this->forcePostRequest();

        $content = Content::findOne(['id' => Yii::$app->request->post('id')]);

        if (!$content) {
            throw new NotFoundHttpException();
        }

        if (!$content->canEdit()) {
            throw new ForbiddenHttpException();
        }

        $form = new AdminDeleteContentForm(['content' => $content]);
        $form->load(Yii::$app->request->post());

        if (!$form->delete()) {
            return $this->asJson(['error' => $form->getErrorsAsString()]);
        }

        return $this->asJson(['success' => true]);
    }

    public function actionReload($id)
    {
        $content = Content::findOne(['id' => $id]);

        if (!$content) {
            throw new NotFoundHttpException(Yii::t('ContentModule.base', 'Invalid content id given!'));
        }

        if (!$content->canView()) {
            throw new ForbiddenHttpException();
        }

        return StreamEntryResponse::getAsJson($content, WallStreamEntryOptions::getInstanceFromRequest());
    }

    /**
     * Switches the content visibility for the given content.
     *
     * @param int $id content id
     * @return Response
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws IntegrityException
     */
    public function actionToggleVisibility($id)
    {
        $this->forcePostRequest();
        $content = Content::findOne(['id' => $id]);

        if (!$content) {
            throw new NotFoundHttpException(Yii::t('ContentModule.base', 'Invalid content id given!'));
        }

        if (!$content->canEdit()) {
            throw new ForbiddenHttpException();
        }

        // Prevent Change to "Public" in private spaces
        if (
            $content->container
            && $content->isPrivate()
            && (
                !$content->container->visibility
                || !$content->container->permissionManager->can(new CreatePublicContent())
            )
        ) {
            throw new ForbiddenHttpException();
        }

        $content->visibility = $content->isPrivate()
            ? Content::VISIBILITY_PUBLIC
            : Content::VISIBILITY_PRIVATE;

        return $this->asJson([
            'success' => $content->save(),
            'state' => $content->visibility,
        ]);
    }

    /**
     * Switch status to lock/unlock comments for the given content.
     *
     * @param int $id Content id
     * @param bool $lockComments True to lock comments, False to unlock
     * @return Response
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws IntegrityException
     */
    public function switchCommentsStatus(int $id, bool $lockComments): Response
    {
        $this->forcePostRequest();
        $content = Content::findOne(['id' => $id]);

        if (!$content) {
            throw new NotFoundHttpException(Yii::t('ContentModule.base', 'Invalid content id given!'));
        } elseif (!$content->canLockComments()) {
            throw new ForbiddenHttpException();
        }

        $content->locked_comments = $lockComments;

        return $this->asJson([
            'success' => $content->save(),
        ]);
    }

    /**
     * Lock comments for the given content.
     *
     * @param int $id Content id
     * @return Response
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws IntegrityException
     */
    public function actionLockComments($id)
    {
        return $this->switchCommentsStatus($id, true);
    }

    /**
     * Unlock comments for the given content.
     *
     * @param int $id Content id
     * @return Response
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws IntegrityException
     */
    public function actionUnlockComments($id)
    {
        return $this->switchCommentsStatus($id, false);
    }

    /**
     * Pins an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function actionPin()
    {
        $this->forcePostRequest();

        $content = Content::findOne(['id' => Yii::$app->request->get('id', '')]);

        if (!$content) {
            throw new NotFoundHttpException();
        }

        if (!$content->canPin()) {
            throw new ForbiddenHttpException();
        }

        $json = ['success' => false];

        /** @var Module $module */
        $module = Yii::$app->getModule('content');
        $maxPinnedContent = $module->getMaxPinnedContent($content->container);

        if ($content->countPinnedItems() < $module->getMaxPinnedContent($content->container)) {
            $content->pin();
            $json['success'] = true;
            $json['contentId'] = $content->id;
        } else {
            $json['info'] = Yii::t('ContentModule.base', "Maximum number of pinned items reached!<br>You can only pin up to {count} items at once.", ['count' => $maxPinnedContent]);
        }

        return $this->asJson($json);
    }

    /**
     * Unpins an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionUnPin()
    {
        $this->forcePostRequest();

        $json = [];
        $json['success'] = false;

        $content = Content::findOne(['id' => Yii::$app->request->get('id', '')]);
        if ($content !== null && $content->canPin()) {
            $content->unpin();
            $json['success'] = true;
        }

        return $this->asJson($json);
    }

    public function actionPublishDraft()
    {
        $this->forcePostRequest();

        $json = [];
        $json['success'] = false;

        $content = Content::findOne(['id' => Yii::$app->request->get('id', '')]);
        if ($content !== null && $content->canEdit() && $content->getStateService()->isDraft()) {
            if ($content->getStateService()->publish()) {
                $json['message'] = Yii::t('ContentModule.base', 'The content has been successfully published.');
                $json['success'] = true;
            } else {
                $json['error'] = Yii::t('ContentModule.base', 'The content cannot be published!');
                $json['success'] = false;
            }
        }

        return $this->asJson($json);
    }

    public function actionNotificationSwitch()
    {
        $this->forcePostRequest();

        $json = [];
        $json['success'] = false; // default

        $content = Content::findOne(['id' => Yii::$app->request->get('id', '')]);
        if ($content !== null) {
            $switch = (Yii::$app->request->get('switch', true) == 1) ? true : false;
            $obj = $content->getPolymorphicRelation();
            $obj->follow(Yii::$app->user->id, $switch);
            $json['success'] = true;
        }

        return $this->asJson($json);
    }

    public function actionScheduleOptions($id = null)
    {
        $this->forcePostRequest();

        $content = $id ? Content::findOne($id) : null;

        if ($content instanceof Content && !$content->canEdit()) {
            throw new ForbiddenHttpException();
        }

        $scheduleOptions = new ScheduleOptionsForm(['content' => $content]);

        if ($scheduleOptions->load(Yii::$app->request->post())) {
            // Disable in order to don't focus the date field because modal window will be closed anyway
            $disableInputs = $scheduleOptions->save();
        } else {
            $disableInputs = !$scheduleOptions->enabled;
        }

        return $this->renderAjax('scheduleOptions', [
            'scheduleOptions' => $scheduleOptions,
            'disableInputs' => $disableInputs,
        ]);
    }

    /**
     * Triggered after content creation
     */
    public function actionRedirectToContentContainer($contentId)
    {
        $content = Content::findOne(['id' => $contentId]);
        if ($content === null) {
            throw new HttpException(404, 'Content not found!');
        }
        return $this->redirect($content->container->getUrl());
    }
}
