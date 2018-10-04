<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use humhub\components\behaviors\AccessControl;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\components\ContentContainerControllerAccess;
use humhub\modules\space\jobs\AddUsersToSpaceJob;
use humhub\modules\space\models\forms\InviteForm;
use humhub\modules\space\models\forms\RequestMembershipForm;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\permissions\InviteUsers;
use humhub\modules\user\models\UserPicker;
use humhub\modules\user\widgets\UserListBox;
use humhub\widgets\ModalClose;
use Yii;
use yii\web\HttpException;

/**
 * SpaceController is the main controller for spaces.
 *
 * It show the space itself and handles all related tasks like following or
 * memberships.
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class MembershipController extends ContentContainerController
{
    public function getAccessRules()
    {
        return [
            ['permission' => [InviteUsers::class], 'actions' => ['invite']],
            [ContentContainerControllerAccess::RULE_LOGGED_IN_ONLY => ['revoke-membership']],
            [ContentContainerControllerAccess::RULE_USER_GROUP_ONLY => [Space::USERGROUP_MEMBER],
                'actions' => [
                    'revoke-notifications',
                    'receive-notifications',
                    'search-invite',
                    'switch-dashboard-display'
                    ]
            ]
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
        if ($visibility === Space::VISIBILITY_NONE && !$space->isMember() ||
            ($visibility === Space::VISIBILITY_REGISTERED_ONLY && Yii::$app->user->isGuest)
        ) {
            throw new HttpException(404, Yii::t('SpaceModule.controllers_SpaceController',
                'This action is only available for workspace members!'));
        }

        return UserPicker::filter([
            'query' => $space->getMembershipUser(),
            'keyword' => Yii::$app->request->get('keyword'),
            'fillUser' => true,
            'disabledText' => Yii::t('SpaceModule.controllers_SpaceController',
                'This user is not a member of this space.'),
        ]);
    }

    /**
     * Requests Membership for this Space
     */
    public function actionRequestMembership()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        if (!$space->canJoin(Yii::$app->user->id)) {
            throw new HttpException(500,
                Yii::t('SpaceModule.controllers_SpaceController', 'You are not allowed to join this space!'));
        }

        $space->addMember(Yii::$app->user->id);

        return $this->htmlRedirect($space->getUrl());
    }

    /**
     * Requests Membership Form for this Space
     * (If a message is required.)
     *
     */
    public function actionRequestMembershipForm()
    {
        $space = $this->getSpace();

        // Check if we have already some sort of membership
        if (Yii::$app->user->isGuest || $space->getMembership(Yii::$app->user->id) != null) {
            throw new HttpException(500,
                Yii::t('SpaceModule.controllers_SpaceController', 'Could not request membership!'));
        }

        $model = new RequestMembershipForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $space->requestMembership(Yii::$app->user->id, $model->message);
            return $this->renderAjax('requestMembershipSave', ['space' => $space]);
        }

        return $this->renderAjax('requestMembership', ['model' => $model, 'space' => $space]);
    }

    public function actionRevokeNotifications()
    {
        $space = $this->getSpace();
        Yii::$app->notification->setSpaceSetting(Yii::$app->user->getIdentity(), $space, false);

        return $this->redirect($space->getUrl());
    }

    public function actionReceiveNotifications()
    {
        $space = $this->getSpace();
        Yii::$app->notification->setSpaceSetting(Yii::$app->user->getIdentity(), $space, true);

        return $this->redirect($space->getUrl());
    }

    /**
     * Revokes Membership for this workspace
     * @throws HttpException
     */
    public function actionRevokeMembership()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        if ($space->isSpaceOwner()) {
            throw new HttpException(500,
                Yii::t('SpaceModule.controllers_SpaceController', 'As owner you cannot revoke your membership!'));
        } elseif (!$space->canLeave()) {
            throw new HttpException(500,
                Yii::t('SpaceModule.controllers_SpaceController', 'Sorry, you are not allowed to leave this space!'));
        }

        $space->removeMember();

        return $this->goHome();
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
            'disabledText' => Yii::t('SpaceModule.controllers_SpaceController',
                'This user is already a member of this space.'),
        ]));
    }


    /**
     * Invite New Members to this workspace
     */
    public function actionInvite()
    {
        $model = new InviteForm(['space' => $this->getSpace()]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if($model->isQueuedJob()) {
                $success = ($model->withoutInvite)
                    ? Yii::t( 'SpaceModule.base', 'User memberships have been added to the queue')
                    : Yii::t( 'SpaceModule.base', 'User invitations have been added to the queue');
            } else {
                $success = Yii::t('SpaceModule.views_space_statusInvite', 'Users has been invited.');
            }

            return ModalClose::widget([
                'success' => $success
            ]);
        }

        return $this->renderAjax('invite', ['model' => $model, 'space' => $model->space]);
    }

    /**
     * When a user clicks on the Accept Invite Link, this action is called.
     * After this the user should be member of this workspace.
     */
    public function actionInviteAccept()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        // Load Pending Membership
        $membership = $space->getMembership();
        if ($membership == null) {
            throw new HttpException(404, Yii::t('SpaceModule.controllers_SpaceController', 'There is no pending invite!'));
        }

        // Check there are really an Invite
        if ($membership->status == Membership::STATUS_INVITED) {
            $space->addMember(Yii::$app->user->id);
        }

        return $this->redirect($space->getUrl());
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
        return $this->renderAjaxContent(UserListBox::widget([
            'query' => Membership::getSpaceMembersQuery($this->getSpace())->visible(),
            'title' => Yii::t('SpaceModule.controllers_MembershipController', "<strong>Members</strong>"),
        ]));
    }

}
