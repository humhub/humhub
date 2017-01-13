<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use Yii;
use yii\web\HttpException;
use humhub\modules\user\models\UserPicker;
use humhub\modules\space\models\Space;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\forms\RequestMembershipForm;
use humhub\modules\user\widgets\UserListBox;

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
class MembershipController extends \humhub\modules\content\components\ContentContainerController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
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

        if (!$space->isMember()) {
            throw new HttpException(404, Yii::t('SpaceModule.controllers_SpaceController', 'This action is only available for workspace members!'));
        }

        return UserPicker::filter([
                    'query' => $space->getMembershipUser(),
                    'keyword' => Yii::$app->request->get('keyword'),
                    'fillUser' => true,
                    'disabledText' => Yii::t('SpaceModule.controllers_SpaceController', 'This user is not a member of this space.')
        ]);
    }

    /**
     * Provides a searchable user list of all workspace members in json.
     *
     */
    public function actionSearchInvite()
    {
        Yii::$app->response->format = 'json';

        $space = $this->getSpace();

        if (!$space->isMember()) {
            throw new HttpException(404, Yii::t('SpaceModule.controllers_SpaceController', 'This action is only available for workspace members!'));
        }

        return UserPicker::filter([
                    'query' => $space->getNonMembershipUser(),
                    'keyword' => Yii::$app->request->get('keyword'),
                    'fillUser' => true,
                    'disabledText' => Yii::t('SpaceModule.controllers_SpaceController', 'This user is already a member of this space.')
        ]);
    }

    /**
     * Requests Membership for this Space
     */
    public function actionRequestMembership()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        if (!$space->canJoin(Yii::$app->user->id))
            throw new HttpException(500, Yii::t('SpaceModule.controllers_SpaceController', 'You are not allowed to join this space!'));

        if ($space->join_policy == Space::JOIN_POLICY_APPLICATION) {
            // Redirect to Membership Request Form
            return $this->redirect($this->createUrl('//space/space/requestMembershipForm', ['sguid' => $this->getSpace()->guid]));
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
            throw new HttpException(500, Yii::t('SpaceModule.controllers_SpaceController', 'Could not request membership!'));
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
     */
    public function actionRevokeMembership()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        if ($space->isSpaceOwner()) {
            throw new HttpException(500, Yii::t('SpaceModule.controllers_SpaceController', 'As owner you cannot revoke your membership!'));
        } elseif (!$space->canLeave()) {
            throw new HttpException(500, Yii::t('SpaceModule.controllers_SpaceController', 'Sorry, you are not allowed to leave this space!'));
        }

        $space->removeMember();

        return $this->goHome();
    }

    /**
     * Invite New Members to this workspace
     */
    public function actionInvite()
    {
        $space = $this->getSpace();

        // Check Permissions to Invite
        if (!$space->canInvite()) {
            throw new HttpException(403, Yii::t('SpaceModule.controllers_MembershipController', 'Access denied - You cannot invite members!'));
        }

        $model = new \humhub\modules\space\models\forms\InviteForm();
        $model->space = $space;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $statusInvite = false;

            // Invite existing members
            foreach ($model->getInvites() as $user) {
                $space->inviteMember($user->id, Yii::$app->user->id);
                $statusInvite = $space->getMembership($user->id)->status;
            }

            // Invite non existing members
            if (Yii::$app->getModule('user')->settings->get('auth.internalUsersCanInvite')) {
                foreach ($model->getInvitesExternal() as $email) {
                    $statusInvite = ($space->inviteMemberByEMail($email, Yii::$app->user->id)) ? Membership::STATUS_INVITED : false;
                }
            }

            switch ($statusInvite) {
                case Membership::STATUS_INVITED:
                    return \humhub\widgets\ModalClose::widget(['success' => Yii::t('SpaceModule.views_space_statusInvite', 'User has been invited.')]);
                case Membership::STATUS_MEMBER:
                    return \humhub\widgets\ModalClose::widget(['success' => Yii::t('SpaceModule.views_space_statusInvite', 'User has become a member.')]);
                default:
                    return \humhub\widgets\ModalClose::widget(['warn' => Yii::t('SpaceModule.views_space_statusInvite', 'User has not been invited.')]);
            }
        }

        return $this->renderAjax('invite', array('model' => $model, 'space' => $space));
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
    public function actionSwitchDashboardDisplay()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        // Load Pending Membership
        $membership = $space->getMembership();
        if ($membership == null) {
            throw new HttpException(404, 'Membership not found!');
        }

        if (Yii::$app->request->get('show') == 0) {
            $membership->show_at_dashboard = 0;
        } else {
            $membership->show_at_dashboard = 1;
        }
        $membership->save();

        return $this->redirect($space->getUrl());
    }

    /**
     * Returns an user list which are space members
     */
    public function actionMembersList()
    {
        $title = Yii::t('SpaceModule.controllers_MembershipController', "<strong>Members</strong>");

        return $this->renderAjaxContent(UserListBox::widget([
                            'query' => Membership::getSpaceMembersQuery($this->getSpace()),
                            'title' => $title
        ]));
    }

}

?>
