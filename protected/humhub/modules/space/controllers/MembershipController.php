<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\components\ContentContainerControllerAccess;
use humhub\modules\space\models\forms\InviteForm;
use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use humhub\modules\space\permissions\InviteUsers;
use humhub\modules\user\models\UserPicker;
use humhub\modules\user\widgets\UserListBox;
use humhub\widgets\modal\ModalClose;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

/**
 * SpaceController is the main controller for spaces.
 *
 * It show the space itself and handles all related tasks like following or
 * memberships.
 *
 * @author Luke
 * @property Module $module
 * @since 0.5
 */
class MembershipController extends ContentContainerController
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permission' => [InviteUsers::class], 'actions' => ['invite', 'search-invite']],
            [ContentContainerControllerAccess::RULE_USER_GROUP_ONLY => [Space::USERGROUP_MEMBER],
                'actions' => [
                    'revoke-notifications',
                    'receive-notifications',
                    'switch-dashboard-display',
                ],
            ],
            [ContentContainerControllerAccess::RULE_AJAX_ONLY => ['members-list']],
        ];
    }

    /**
     * Provides a searchable user list of all workspace members in json.
     *
     */
    public function actionSearch()
    {
        Yii::$app->response->format = 'json';

        $space = $this->getSpace();
        $visibility = (int)$space->visibility;
        if ($visibility === Space::VISIBILITY_NONE && !$space->isMember()
            || ($visibility === Space::VISIBILITY_REGISTERED_ONLY && Yii::$app->user->isGuest)
        ) {
            throw new HttpException(404, Yii::t(
                'SpaceModule.base',
                'This action is only available for workspace members!',
            ));
        }

        return UserPicker::filter([
            'query' => $space->getMembershipUser(),
            'keyword' => Yii::$app->request->get('keyword'),
            'fillUser' => true,
            'disabledText' => Yii::t(
                'SpaceModule.base',
                'This user is not a member of this space.',
            ),
        ]);
    }

    public function actionRevokeNotifications()
    {
        $this->forcePostRequest();

        $space = $this->getSpace();
        Yii::$app->notification->setSpaceSetting(Yii::$app->user->getIdentity(), $space, false);

        return $this->redirect($space->getUrl());
    }

    public function actionReceiveNotifications()
    {
        $this->forcePostRequest();

        $space = $this->getSpace();
        Yii::$app->notification->setSpaceSetting(Yii::$app->user->getIdentity(), $space, true);

        return $this->redirect($space->getUrl());
    }

    /**
     * Provides a searchable user list of all workspace members in json.
     *
     */
    public function actionSearchInvite()
    {
        $space = $this->getSpace();

        return $this->asJson(UserPicker::filter([
            'query' => $space->getNonMembershipUser(),
            'keyword' => Yii::$app->request->get('keyword'),
            'fillUser' => true,
            'disabledText' => Yii::t(
                'SpaceModule.base',
                'This user is already a member of this space.',
            ),
        ]));
    }


    /**
     * Invite New Members to this workspace
     */
    public function actionInvite()
    {
        $model = new InviteForm(['space' => $this->getSpace()]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->isQueuedJob()) {
                $success = ($model->withoutInvite)
                    ? Yii::t('SpaceModule.base', 'User memberships have been added to the queue')
                    : Yii::t('SpaceModule.base', 'User invitations have been added to the queue');
            } else {
                $success = Yii::t('SpaceModule.base', 'Users has been invited.');
            }

            return ModalClose::widget([
                'success' => $success,
            ]);
        }

        return $this->renderAjax('invite', [
            'model' => $model,
            'space' => $model->space,
        ]);
    }


    /**
     * @return string
     * @throws \yii\base\Exception
     * @throws ForbiddenHttpException
     */
    public function actionResetInviteLink()
    {
        $this->forcePostRequest();

        $model = new InviteForm(['space' => $this->getSpace()]);

        if (!$model->space || !$model->space->isAdmin()) {
            $this->forbidden();
        }

        $model->getInviteLink(true);

        $this->view->saved();

        return $this->renderAjax('invite', [
            'model' => $model,
            'space' => $model->space,
        ]);
    }

    /**
     * Toggle space content display at dashboard
     *
     * @throws HttpException
     */
    public function actionSwitchDashboardDisplay($show = 0)
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        $membership = $space->getMembership();
        $membership->show_at_dashboard = ($show) ? 1 : 0;
        $membership->save();

        return $this->redirect($space->getUrl());
    }

    /**
     * Returns an user list which are space members
     */
    public function actionMembersList()
    {
        if (!$this->space->canViewMembers()) {
            throw new HttpException(403);
        }

        return $this->renderAjaxContent(UserListBox::widget([
            'query' => $this->getSpace()->getMemberListService()->getQuery(),
            'title' => Yii::t('SpaceModule.manage', "<strong>Members</strong>"),
        ]));
    }
}
