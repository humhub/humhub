<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use humhub\components\Controller;
use humhub\components\behaviors\AccessControl;
use humhub\modules\space\models\Space;
use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\space\models\forms\InviteForm;
use Colors\RandomColor;
use humhub\modules\user\helpers\AuthHelper;
use Yii;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * CreateController is responsible for creation of new spaces
 *
 * @author Luke
 * @since 0.5
 */
class CreateController extends Controller
{

    /**
     * @inheritdoc
     */
    public $defaultAction = 'create';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
            ]
        ];
    }

    public function actionIndex()
    {
        return $this->redirect(['create']);
    }

    /**
     * Creates a new Space
     * @throws HttpException
     * @throws Exception
     */
    public function actionCreate($visibility = null, $skip = 0)
    {
        // User cannot create spaces (public or private)
        if (!Yii::$app->user->permissionmanager->can(new CreatePublicSpace) && !Yii::$app->user->permissionmanager->can(new CreatePrivateSpace)) {
            throw new HttpException(400, 'You are not allowed to create spaces!');
        }

        $model = $this->createSpaceModel();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($skip) {
                return $this->htmlRedirect($model->getUrl());
            }
            return $this->actionModules($model->id);
        }

        $visibilityOptions = [];
        if (AuthHelper::isGuestAccessEnabled() && Yii::$app->user->permissionmanager->can(new CreatePublicSpace)) {
            $visibilityOptions[Space::VISIBILITY_ALL] = Yii::t('SpaceModule.base', 'Public (Members & Guests)');
        }
        if (Yii::$app->user->permissionmanager->can(new CreatePublicSpace)) {
            $visibilityOptions[Space::VISIBILITY_REGISTERED_ONLY] = Yii::t('SpaceModule.base', 'Public (Members only)');
        }
        if (Yii::$app->user->permissionmanager->can(new CreatePrivateSpace)) {
            $visibilityOptions[Space::VISIBILITY_NONE] = Yii::t('SpaceModule.base', 'Private (Invisible)');
        }

        if ($visibility !== null && isset($visibilityOptions[$visibility])) {
            // allow setting pre-selected visibility
            $model->visibility = $visibility;
        } elseif (!isset($visibilityOptions[$model->visibility])) {
            $model->visibility = array_key_first($visibilityOptions);
        }

        $joinPolicyOptions = [
            Space::JOIN_POLICY_NONE => Yii::t('SpaceModule.base', 'Only by invite'),
            Space::JOIN_POLICY_APPLICATION => Yii::t('SpaceModule.base', 'Invite and request'),
            Space::JOIN_POLICY_FREE => Yii::t('SpaceModule.base', 'Everyone can enter')
        ];

        return $this->renderAjax('create', ['model' => $model, 'visibilityOptions' => $visibilityOptions, 'joinPolicyOptions' => $joinPolicyOptions]);
    }

    /**
     * Creates an empty space model
     *
     * @return Space the preconfigured space object
     */
    protected function createSpaceModel()
    {
        /* @var \humhub\modules\space\Module $module */
        $module = Yii::$app->getModule('space');

        $model = new Space();
        $model->scenario = Space::SCENARIO_CREATE;
        $model->visibility = $module->settings->get('defaultVisibility', Space::VISIBILITY_REGISTERED_ONLY);
        $model->join_policy = $module->settings->get('defaultJoinPolicy', Space::JOIN_POLICY_APPLICATION);
        $model->color = RandomColor::one(['luminosity' => 'dark']);

        return $model;
    }

    /**
     * Activate / deactivate modules
     * @throws Exception
     */
    public function actionModules($space_id)
    {
        $space = Space::find()->where(['id' => $space_id])->one();

        if (count($space->getAvailableModules()) == 0) {
            return $this->actionInvite($space);
        } else {
            return $this->renderAjax('modules', ['space' => $space, 'availableModules' => $space->getAvailableModules()]);
        }
    }

    /**
     * Invite user
     *
     * @throws Exception
     */
    public function actionInvite($space = null, $spaceId = null)
    {
        $space = ($space == null) ? Space::findOne(['id' => $spaceId]) : $space;

        if(!$space) {
            throw new HttpException(404);
        }

        $model = new InviteForm(['space' => $space]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->htmlRedirect($space->getUrl());
        }

        return $this->renderAjax('invite', [
            'canInviteExternal' => $model->canInviteExternal(),
            'model' => $model,
            'space' => $space
        ]);
    }

}
