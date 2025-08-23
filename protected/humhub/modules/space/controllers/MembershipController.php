<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use Exception;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\components\ContentContainerControllerAccess;
use humhub\modules\space\models\forms\InviteForm;
use humhub\modules\space\models\forms\RequestMembershipForm;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use humhub\modules\space\permissions\InviteUsers;
use humhub\modules\space\widgets\MembershipButton;
use humhub\modules\user\models\UserPicker;
use humhub\modules\user\widgets\UserListBox;
use humhub\widgets\ModalClose;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;

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
            ['permission' => [InviteUsers::class], 'actions' => ['invite']],
            [ContentContainerControllerAccess::RULE_LOGGED_IN_ONLY => ['revoke-membership']],
            [ContentContainerControllerAccess::RULE_USER_GROUP_ONLY => [Space::USERGROUP_MEMBER],
                'actions' => [
                    'revoke-notifications',
                    'receive-notifications',
                    'search-invite',
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

    /**
     * Requests Membership for this Space
     */
    public function actionRequestMembership()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        if (!$space->canJoin(Yii::$app->user->id)) {
            throw new HttpException(
                500,
                Yii::t('SpaceModule.base', 'You are not allowed to join this space!'),
            );
        }

        $space->addMember(Yii::$app->user->id);

        return $this->getActionResult($space);
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
            throw new HttpException(
                500,
                Yii::t('SpaceModule.base', 'Could not request membership!'),
            );
        }

        $model = new RequestMembershipForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $space->requestMembership(Yii::$app->user->id, $model->message);

            return $this->renderAjax('requestMembershipSave', [
                'spaceId' => $space->id,
                'newMembershipButton' => MembershipButton::widget([
                    'space' => $space,
                    'options' => empty($model->options) ? [] : Json::decode($model->options),
                ]),
            ]);
        }

        $model->options = $this->request->get('options');

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
     * @return Response
     * @throws HttpException
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function actionRevokeMembership()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        if ($space->isSpaceOwner()) {
            throw new HttpException(
                500,
                Yii::t('SpaceModule.base', 'As owner you cannot revoke your membership!'),
            );
        } elseif (!$space->canLeave()) {
            throw new HttpException(
                500,
                Yii::t('SpaceModule.base', 'Sorry, you are not allowed to leave this space!'),
            );
        }

        $space->removeMember();

        return $this->getActionResult($space);
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
            throw new HttpException(404, Yii::t('SpaceModule.base', 'There is no pending invite!'));
        }

        // Check there are really an Invite
        if ($membership->status == Membership::STATUS_INVITED) {
            $space->addMember(Yii::$app->user->id);
        }

        return $this->getActionResult($space);
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
        if (!$this->canViewMembers()) {
            throw new HttpException(403);
        }

        return $this->renderAjaxContent(UserListBox::widget([
            'query' => $this->getSpace()->getMemberListService()->getQuery(),
            'title' => Yii::t('SpaceModule.manage', "<strong>Members</strong>"),
        ]));
    }

    /**
     * Get result for the membership actions
     *
     * @param Space $space
     * @return string|\yii\console\Response|Response
     * @throws Exception
     */
    protected function getActionResult(Space $space)
    {
        if ($this->request->isAjax && !Yii::$app->request->get('redirect', false)) {
            $options = $this->request->post('options', []);

            // Show/Hide the "Follow"/"Unfollow" buttons depending on updated membership state after AJAX action
            if ($space->isMember()) {
                $options['cancelMembership']['attrs']['data-hide-buttons'] = '.followButton, .unfollowButton';
            } else {
                $options['becomeMember']['attrs']['data-show-buttons'] = $space->isFollowedByUser() ? '.unfollowButton' : '.followButton';
                $options['becomeMember']['attrs']['data-hide-buttons'] = $space->isFollowedByUser() ? '.followButton' : '.unfollowButton';
            }

            return MembershipButton::widget([
                'space' => $space,
                'options' => $options,
            ]);
        }

        return $this->redirect($this->request->getReferrer());
    }

    private function canViewMembers(): bool
    {
        if ($this->space->getAdvancedSettings()->hideMembers) {
            $membership = $this->space->getMembership();
            return $membership->isPrivileged() ?? false;
        }

        return true;
    }
}
