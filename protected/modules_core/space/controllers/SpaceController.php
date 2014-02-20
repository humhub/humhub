<?php

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
class SpaceController extends Controller {

    public $subLayout = "_layout";

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors() {
        return array(
            'SpaceControllerBehavior' => array(
                'class' => 'application.modules_core.space.SpaceControllerBehavior',
            ),
        );
    }

    /**
     * Generic Start Action for Profile
     */
    public function actionIndex() {
        $this->pageTitle = HSetting::Get('name') . " - " . $this->getSpace()->name;


        if ($this->getSpace()->isMember()) {

            $this->render('index', array());
        } else {

            $this->subLayout = "_layoutPublic";

            $this->render('indexPublic', array('space' => $this->getSpace()));
        }
    }

    /**
     * List Members of the Space
     *
     */
    public function actionMembers() {
        $this->getSpace();
        $this->render('members');
    }

    /**
     * Follows a Space
     */
    public function actionFollow() {

        $space = $this->getSpace();
        SpaceFollow::follow($space->id, Yii::app()->user->id);

        $this->redirect($space->getUrl());
    }

    /**
     * Unfollows a Space
     */
    public function actionUnfollow() {

        $space = $this->getSpace();
        SpaceFollow::unfollow($space->id, Yii::app()->user->id);

        $this->redirect($space->getUrl());
    }

    /**
     * Provides a searchable user list of all workspace members in json.
     *
     */
    public function actionSearchMemberJson() {

        $space = $this->getSpace();

        if (!$space->isMember()) {
            throw new CHttpException(404, Yii::t('SpaceModule.base', 'This action is only available for workspace members!'));
        }

        $maxResults = 10;
        $results = array();
        $keyword = Yii::app()->request->getParam('keyword');
        $keyword = Yii::app()->input->stripClean($keyword);


        // Build Search Condition
        $params = array();
        $condition = "user_space_membership.status=" . UserSpaceMembership::STATUS_MEMBER;
        $condition .= " AND space_id=" . $space->id;
        $parts = explode(" ", $keyword);
        $i = 0;
        foreach ($parts as $part) {
            $i++;
            $condition .= " AND (email LIKE :match{$i} OR username LIKE :match{$i})";
            $params[':match' . $i] = "%" . $part . "%";
        }

        $sql = "SELECT DISTINCT user.* FROM user_space_membership
                LEFT JOIN user ON user.id=user_space_membership.user_id
                WHERE " . $condition . " LIMIT 0," . $maxResults;

        $users = User::model()->findAllBySql($sql, $params);

        foreach ($users as $user) {
            $userInfo['guid'] = $user->guid;
            $userInfo['displayName'] = $user->displayName;
            $userInfo['email'] = $user->email;
            $userInfo['image'] = $user->getProfileImage()->getUrl();
            $userInfo['link'] = $user->getProfileUrl();
            $results[] = $userInfo;
        }

        print CJSON::encode($results);
        Yii::app()->end();
    }

    /**
     * Requests Membership for this Space
     */
    public function actionRequestMembership() {

        $space = $this->getSpace();

        if (!$space->canJoin(Yii::app()->user->id))
            throw new CHttpException(500, Yii::t('SpaceModule.base', 'You are not allowed to join this space!'));

        if ($space->join_policy == Space::JOIN_POLICY_APPLICATION) {
            // Redirect to Membership Request Form
            return $this->redirect($this->createUrl('//space/space/requestMembershipForm', array('sguid' => $this->getSpace()->guid)));
        }

        $space->addMember(Yii::app()->user->id);
        return $this->htmlRedirect($space->getUrl());
    }

    /**
     * Requests Membership Form for this Space
     * (If a message is required.)
     *
     */
    public function actionRequestMembershipForm() {

        $space = $this->getSpace();

        // Check if we have already some sort of membership
        if ($space->getUserMembership(Yii::app()->user->id) != null) {
            throw new CHttpException(500, Yii::t('SpaceModule.base', 'Could not request membership!'));
        }

        $model = new SpaceRequestMembershipForm;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'workspace-apply-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['SpaceRequestMembershipForm'])) {

            $_POST['SpaceRequestMembershipForm'] = Yii::app()->input->stripClean($_POST['SpaceRequestMembershipForm']);

            $model->attributes = $_POST['SpaceRequestMembershipForm'];

            if ($model->validate()) {

                $space->requestMembership(Yii::app()->user->id, $model->message);

                $output = $this->renderPartial('requestMembershipSave', array('model' => $model, 'workspace' => $space));
                Yii::app()->clientScript->render($output);
                echo $output;
                Yii::app()->end();
                return;
            }
        }

        $output = $this->renderPartial('requestMembership', array('model' => $model, 'space' => $space));
        Yii::app()->clientScript->render($output);
        echo $output;
        Yii::app()->end();
    }

    /**
     * Revokes Membership for this workspace
     */
    public function actionRevokeMembership() {

        $space = $this->getSpace();

        if ($space->isOwner()) {
            throw new CHttpException(500, Yii::t('SpaceModule.base', 'As owner you cannot revoke your membership!'));
        }

        $space->removeMember();
        $this->redirect($this->createUrl('//'));
    }

    /**
     * Invite New Members to this workspace
     */
    public function actionInvite() {

        $space = $this->getSpace();

        // Check Permissions to Invite
        if (!$space->canInvite()) {
            throw new CHttpException(403, 'Access denied - You cannot invite members!');
        }

        $model = new SpaceInviteForm();
        $model->space = $space;

        if (isset($_POST['SpaceInviteForm'])) {

            $_POST['SpaceInviteForm'] = Yii::app()->input->stripClean($_POST['SpaceInviteForm']);
            $model->attributes = $_POST['SpaceInviteForm'];

            if ($model->validate()) {

                // Invite existing members
                foreach ($model->getInvites() as $user) {
                    $space->inviteMember($user->id, Yii::app()->user->id);
                }

                if (HSetting::Get('internalUsersCanInvite', 'authentication_internal')) {
                    // Invite non existing members
                    foreach ($model->getInvitesExternal() as $email) {
                        $space->inviteMemberByEMail($email, Yii::app()->user->id);
                    }
                }

                // close modal
                $this->renderModalClose();
            }
        }

        $output = $this->renderPartial('invite', array('model' => $model, 'space' => $space));
        Yii::app()->clientScript->render($output);
        echo $output;
        Yii::app()->end();
    }

    /**
     * When a user clicks on the Accept Invite Link, this action is called.
     * After this the user should be member of this workspace.
     */
    public function actionInviteAccept() {

        // Get Current Space
        $space = $this->getSpace();

        // Load Pending Membership
        $membership = UserSpaceMembership::model()->findByAttributes(array('user_id' => Yii::app()->user->id, 'space_id' => $space->id));
        if ($membership == null) {
            throw new CHttpException(404, Yii::t('SpaceModule.base', 'There is no pending invite!'));
        }

        // Check there are really an Invite
        if ($membership->status == UserSpaceMembership::STATUS_INVITED) {
            $space->addMember(Yii::app()->user->id);
        }

        $this->redirect($space->getUrl());
    }

}

?>
