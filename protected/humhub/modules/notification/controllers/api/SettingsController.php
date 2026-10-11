<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\notification\services\NotificationSettingsService;
use humhub\modules\notification\services\NotificationSpaceService;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

/**
 * The notification settings API (see `docs/develop/concept-api.md`), consumed by the
 * `NotificationSettings` island of the account and the administration settings pages.
 *
 * The caller's own settings; with `?scope=global` the administrator's defaults for everyone
 * (`ManageSettings`). The payload is built and applied by
 * {@see NotificationSettingsService::toArray()}/{@see NotificationSettingsService::fromArray()},
 * which the settings page uses for its first paint as well.
 *
 * @since 1.20
 */
class SettingsController extends BaseController
{
    public const SCOPE_GLOBAL = 'global';

    /**
     * @inheritdoc
     */
    protected bool $allowSessionAuth = true;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET', 'HEAD'],
                    'update' => ['PATCH'],
                    'reset' => ['POST'],
                    'reset-all' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * The settings of the caller, or with `?scope=global` the defaults for everyone.
     */
    public function actionIndex()
    {
        return $this->getService()->toArray();
    }

    /**
     * Changes the settings of the caller, or with `?scope=global` the defaults for everyone -
     * partially: what the body leaves out is not changed. Answers the settings as
     * {@see actionIndex()}, `422 {errors}` when anything is invalid.
     */
    public function actionUpdate()
    {
        $service = $this->getService();

        $errors = $service->fromArray((array)Yii::$app->request->getBodyParams());
        if ($errors !== []) {
            return $this->validationErrors($errors);
        }

        return $service->toArray();
    }

    /**
     * Resets the caller's settings to the defaults - the category switches and the
     * space selection - and answers the settings as {@see actionIndex()}.
     */
    public function actionReset()
    {
        $user = Yii::$app->user->getIdentity();
        $service = new NotificationSettingsService($user);

        Yii::$app->db->transaction(function () use ($service, $user): void {
            $service->reset();
            (new NotificationSpaceService())->setSpaces([], $user);
            Yii::$app->getModule('notification')->settings->user($user)->delete(NotificationSpaceService::IS_TOUCHED_SETTINGS);
        });

        return $service->toArray();
    }

    /**
     * Resets the settings of every user to the defaults; requires `ManageSettings` (the page it
     * is offered on) and `ManageUsers`.
     */
    public function actionResetAll()
    {
        if (!Yii::$app->user->can(ManageSettings::class) || !Yii::$app->user->can(ManageUsers::class)) {
            throw new ForbiddenHttpException();
        }

        NotificationSettingsService::resetAllUsers();

        return (new NotificationSettingsService())->toArray();
    }

    /**
     * The service of the requested scope: the caller's, or with `?scope=global` the global one.
     *
     * @throws ForbiddenHttpException for the global scope without `ManageSettings`
     */
    private function getService(): NotificationSettingsService
    {
        if (Yii::$app->request->get('scope') === self::SCOPE_GLOBAL) {
            if (!Yii::$app->user->can(ManageSettings::class)) {
                throw new ForbiddenHttpException();
            }

            return new NotificationSettingsService();
        }

        return new NotificationSettingsService(Yii::$app->user->getIdentity());
    }
}
