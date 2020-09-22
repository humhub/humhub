<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\modules\admin\models\forms\SpaceSettingsForm;
use humhub\modules\admin\models\SpaceSearch;
use humhub\modules\content\models\Content;
use humhub\modules\space\models\Space;
use humhub\modules\user\helpers\AuthHelper;
use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageSettings;
use yii\web\HttpException;

/**
 * SpaceController provides global space administration.
 *
 * @since 0.5
 */
class SpaceController extends Controller
{

    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->subLayout = '@admin/views/layouts/space';
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Spaces'));

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => [ManageSpaces::class, ManageSettings::class]],
        ];
    }

    /**
     * Shows all available spaces
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->can(new ManageSpaces())) {
            return $this->redirect(['settings']);
        }

        $searchModel = new SpaceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel
        ]);
    }

    /**
     * Deep link into space
     * @throws HttpException
     */
    public function actionOpen($id, $section = null)
    {
        $space = Space::findOne(['id' => $id]);
        if ($space === null) {
            throw new HttpException(404);
        }

        if ($section == 'members') {
            return $this->redirect($space->createUrl('/space/manage/member'));
        } elseif ($section == 'owner') {
            return $this->redirect($space->createUrl('/space/manage/member/change-owner'));
        } elseif ($section == 'edit') {
            return $this->redirect($space->createUrl('/space/manage'));
        } elseif ($section == 'modules') {
            return $this->redirect($space->createUrl('/space/manage/module'));
        } elseif ($section == 'delete') {
            return $this->redirect($space->createUrl('/space/manage/default/delete'));
        } else {
            return $this->redirect($space->getUrl());
        }
    }

    /**
     * General Space Settings
     */
    public function actionSettings()
    {
        $form = new SpaceSettingsForm;
        $visibilityOptions = [];

        if (AuthHelper::isGuestAccessEnabled()) {
            $visibilityOptions[Space::VISIBILITY_ALL] = Yii::t('SpaceModule.base', 'Public (Members & Guests)');
        }

        $visibilityOptions[Space::VISIBILITY_REGISTERED_ONLY] = Yii::t('SpaceModule.base', 'Public (Members only)');
        $visibilityOptions[Space::VISIBILITY_NONE] = Yii::t('SpaceModule.base', 'Private (Invisible)');

        $joinPolicyOptions = [
            Space::JOIN_POLICY_NONE => Yii::t('SpaceModule.base', 'Only by invite'),
            Space::JOIN_POLICY_APPLICATION => Yii::t('SpaceModule.base', 'Invite and request'),
            Space::JOIN_POLICY_FREE => Yii::t('SpaceModule.base', 'Everyone can enter')
        ];

        $contentVisibilityOptions = [
            Content::VISIBILITY_PRIVATE => Yii::t('SpaceModule.base', 'Private'),
            Content::VISIBILITY_PUBLIC => Yii::t('SpaceModule.base', 'Public')];

        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect(['settings']);
        }

        return $this->render('settings', [
                    'model' => $form,
                    'joinPolicyOptions' => $joinPolicyOptions,
                    'visibilityOptions' => $visibilityOptions,
                    'contentVisibilityOptions' => $contentVisibilityOptions
                        ]
        );
    }

}
