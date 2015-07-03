<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\core\space\controllers;

use Yii;
use \humhub\components\Controller;
use \yii\helpers\Url;
use \yii\web\HttpException;
use \humhub\core\user\models\User;
use humhub\core\space\models\Space;
use humhub\models\Setting;
use humhub\core\space\models\Membership;
use humhub\core\space\models\forms\RequestMembershipForm;

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
class MembershipController extends \humhub\core\content\components\ContentContainerController
{

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

        $results = array();
        $keyword = Yii::$app->request->get('keyword');


        $query = User::find();
        $query->leftJoin('space_membership', 'space_membership.user_id=user.id AND space_membership.space_id=:space_id AND space_membership.status=:member', ['space_id' => $space->id, 'member' => Membership::STATUS_MEMBER]);
        $query->andWhere('space_membership.space_id IS NOT NULL');


        $query->joinWith('profile');
        $query->limit(10);

        // Build Search Condition
        $parts = explode(" ", $keyword);
        $i = 0;
        foreach ($parts as $part) {
            $i++;
            $query->andWhere("(user.email LIKE :match OR "
                    . "user.username LIKE :match OR "
                    . "profile.firstname LIKE :match OR "
                    . "profile.lastname LIKE :match OR "
                    . "profile.title LIKE :match)", ['match' => '%' . $part . '%']);
        }

        foreach ($query->all() as $user) {
            $userInfo['guid'] = $user->guid;
            $userInfo['displayName'] = \yii\helpers\Html::encode($user->displayName);
            $userInfo['email'] = $user->email;
            $userInfo['image'] = $user->getProfileImage()->getUrl();
            $userInfo['link'] = $user->getUrl();
            $results[] = $userInfo;
        }

        return $results;
    }

    /**
     * Requests Membership for this Space
     */
    public function actionRequestMembership()
    {

        $space = $this->getSpace();

        if (!$space->canJoin(Yii::$app->user->id))
            throw new HttpException(500, Yii::t('SpaceModule.controllers_SpaceController', 'You are not allowed to join this space!'));

        if ($space->join_policy == Space::JOIN_POLICY_APPLICATION) {
            // Redirect to Membership Request Form
            return $this->redirect($this->createUrl('//space/space/requestMembershipForm', array('sguid' => $this->getSpace()->guid)));
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
            return $this->renderAjax('requestMembershipSave');
        }

        return $this->renderAjax('requestMembership', ['model' => $model, 'space' => $space]);
    }

    /**
     * Revokes Membership for this workspace
     */
    public function actionRevokeMembership()
    {
        $space = $this->getSpace();

        if ($space->isSpaceOwner()) {
            throw new HttpException(500, Yii::t('SpaceModule.controllers_SpaceController', 'As owner you cannot revoke your membership!'));
        }

        $space->removeMember();

        return $this->redirect(Url::home());
    }

    /**
     * Invite New Members to this workspace
     */
    public function actionInvite()
    {
        $space = $this->getSpace();

        // Check Permissions to Invite
        if (!$space->canInvite()) {
            throw new HttpException(403, 'Access denied - You cannot invite members!');
        }

        $model = new \humhub\core\space\models\forms\InviteForm();
        $model->space = $space;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // Invite existing members
            foreach ($model->getInvites() as $user) {
                $space->inviteMember($user->id, Yii::$app->user->id);
                $statusInvite = $space->getMembership($user->id)->status;
            }

            // Invite non existing members
            if (Setting::Get('internalUsersCanInvite', 'authentication_internal')) {
                foreach ($model->getInvitesExternal() as $email) {
                    $statusInvite = ($space->inviteMemberByEMail($email, Yii::$app->user->id)) ? Membership::STATUS_INVITED : false;
                }
            }

            return $this->renderAjax('statusInvite', array('status' => $statusInvite));
        }

        return $this->renderAjax('invite', array('model' => $model, 'space' => $space));
    }

    /**
     * When a user clicks on the Accept Invite Link, this action is called.
     * After this the user should be member of this workspace.
     */
    public function actionInviteAccept()
    {

        // Get Current Space
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

}

?>
