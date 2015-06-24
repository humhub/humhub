<?php

/**
 * AdminController provides all space administration actions.
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class AdminController extends Controller
{

    /**
     * @var String Admin Sublayout
     */
    public $subLayout = "application.modules_core.space.views.space._layout";

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
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
    public function behaviors()
    {
        return array(
            'ProfileControllerBehavior' => array(
                'class' => 'application.modules_core.space.behaviors.SpaceControllerBehavior',
            ),
        );
    }

    public function beforeAction($action)
    {

        $this->adminOnly();
        return parent::beforeAction($action);
    }

    /**
     * First Admin Action to display
     */
    public function actionIndex()
    {
        $this->redirect($this->createUrl('edit', array('sguid' => $this->getSpace()->guid)));
    }

    /**
     * Space Edit Form
     *
     * @todo Add Owner Switch Box for the Owner only!
     */
    public function actionEdit()
    {

        $model = $this->getSpace();
        $model->scenario = 'edit';

        // Ajax Validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'space-edit-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['Space'])) {
            $_POST['Space'] = Yii::app()->input->stripClean($_POST['Space']);
            $model->attributes = $_POST['Space'];
            if ($model->validate()) {
                $model->save();
                Yii::app()->user->setFlash('data-saved', Yii::t('SpaceModule.controllers_AdminController', 'Saved'));
                $this->redirect($model->createUrl('admin/edit'));
            }
        }

        $this->render('edit', array('model' => $model));
    }

    /**
     * Members Administration Action
     */
    public function actionMembers()
    {

        $membersPerPage = 10;
        $space = $this->getSpace();

        // User Role Management
        if (isset($_POST['users'])) {

            $users = Yii::app()->request->getParam('users');

            // Loop over all users in Form
            foreach ($users as $userGuid) {
                // Get informations
                if (isset($_POST['user_' . $userGuid])) {
                    $userSettings = Yii::app()->request->getParam('user_' . $userGuid);

                    $user = User::model()->findByAttributes(array('guid' => $userGuid));
                    if ($user != null) {

                        // No changes on the Owner
                        if ($space->isSpaceOwner($user->id))
                            continue;

                        $membership = SpaceMembership::model()->findByAttributes(array('user_id' => $user->id, 'space_id' => $space->id));
                        if ($membership != null) {
                            $membership->invite_role = (isset($userSettings['inviteRole']) && $userSettings['inviteRole'] == 1) ? 1 : 0;
                            $membership->admin_role = (isset($userSettings['adminRole']) && $userSettings['adminRole'] == 1) ? 1 : 0;
                            $membership->share_role = (isset($userSettings['shareRole']) && $userSettings['shareRole'] == 1) ? 1 : 0;
                            $membership->save();
                        }
                    }
                }
            }

            // Change owner if changed
            if ($space->isSpaceOwner()) {
                $owner = $space->getSpaceOwner();

                $newOwnerId = Yii::app()->request->getParam('ownerId');

                if ($newOwnerId != $owner->id) {
                    if ($space->isMember($newOwnerId)) {
                        $space->setSpaceOwner($newOwnerId);

                        // Redirect to current space
                        $this->redirect($this->createUrl('admin/members', array('sguid' => $this->getSpace()->guid)));
                    }
                }
            }

            Yii::app()->user->setFlash('data-saved', Yii::t('SpaceModule.controllers_AdminController', 'Saved'));
        } // Updated Users


        $criteria = new CDbCriteria;
        $criteria->condition = "1";

        // Allow User Searches
        $search = Yii::app()->request->getQuery('search');
        if ($search != "") {
            $criteria->join = "LEFT JOIN user ON memberships.user_id = user.id ";
            $criteria->condition .= " AND (";
            $criteria->condition .= ' user.username LIKE :search';
            $criteria->condition .= ' OR user.email like :search';
            $criteria->condition .= " ) ";
            $criteria->params = array(':search' => '%' . $search . '%');
        }

        //ToDo: Better Counting
        $allMemberCount = count($space->memberships($criteria));

        $pages = new CPagination($allMemberCount);
        $pages->setPageSize($membersPerPage);
        $pages->applyLimit($criteria);

        $members = $space->memberships($criteria);

        $invited_members = SpaceMembership::model()->findAllByAttributes(array('space_id' => $space->id, 'status' => SpaceMembership::STATUS_INVITED));

        $this->render('members', array(
            'space' => $space,
            'members' => $members, // must be the same as $item_count
            'invited_members' => $invited_members,
            'item_count' => $allMemberCount,
            'page_size' => $membersPerPage,
            'search' => $search,
            'pages' => $pages,
        ));
    }

    /**
     * User Manage Users Page, Reject Member Request Link
     */
    public function actionMembersRejectApplicant()
    {

        $this->forcePostRequest();

        $space = $this->getSpace();
        $userGuid = Yii::app()->request->getParam('userGuid');
        $user = User::model()->findByAttributes(array('guid' => $userGuid));

        if ($user != null) {
            $space->removeMember($user->id);

            SpaceApprovalRequestDeclinedNotification::fire(Yii::app()->user->id, $user, $space);
        }

        $this->redirect($space->getUrl());
    }

    /**
     * User Manage Users Page, Approve Member Request Link
     */
    public function actionMembersApproveApplicant()
    {

        $this->forcePostRequest();

        $space = $this->getSpace();
        $userGuid = Yii::app()->request->getParam('userGuid');
        $user = User::model()->findByAttributes(array('guid' => $userGuid));

        if ($user != null) {
            $membership = $space->getMembership($user->id);
            if ($membership != null && $membership->status == SpaceMembership::STATUS_APPLICANT) {
                $space->addMember($user->id);
            }
        }


        $this->redirect($space->getUrl());
    }

    /**
     * Removes a Member
     */
    public function actionRemoveMember()
    {
        $this->forcePostRequest();

        $workspace = $this->getSpace();
        $userGuid = Yii::app()->request->getParam('userGuid');
        $user = User::model()->findByAttributes(array('guid' => $userGuid));

        if ($workspace->isSpaceOwner($user->id)) {
            throw new CHttpException(500, 'Owner cannot be removed!');
        }

        $workspace->removeMember($user->id);

        // Redirect  back to Administration page
        $this->htmlRedirect($this->createUrl('//space/admin/members', array('sguid' => $workspace->guid)));
    }

    /**
     * Handle the profile image upload
     */
    public function actionImageUpload()
    {
        $space = $this->getSpace();
        $model = new UploadProfileImageForm();
        $json = array();

        //$model->image = CUploadedFile::getInstance($model, 'image');
        $files = CUploadedFile::getInstancesByName('spacefiles');
        $file = $files[0];
        $model->image = $file;

        if ($model->validate()) {

            $json['error'] = false;

            $profileImage = new ProfileImage($space->guid);
            $profileImage->setNew($model->image);

            $json['name'] = "";
            $json['url'] = $profileImage->getUrl();
            $json['size'] = $model->image->getSize();
            $json['deleteUrl'] = "";
            $json['deleteType'] = "";
        } else {
            $json['error'] = true;
            $json['errors'] = $model->getErrors();
        }

        return $this->renderJson(array('files' => $json));
    }

    /**
     * Crops the profile image of the user
     */
    public function actionCropImage()
    {
        $space = $this->getSpace();

        $model = new CropProfileImageForm;
        $profileImage = new ProfileImage($space->guid);

        if (isset($_POST['CropProfileImageForm'])) {
            $_POST['CropProfileImageForm'] = Yii::app()->input->stripClean($_POST['CropProfileImageForm']);
            $model->attributes = $_POST['CropProfileImageForm'];
            if ($model->validate()) {
                $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
                $this->htmlRedirect();
            }
        }

        $output = $this->renderPartial('cropImage', array('model' => $model, 'profileImage' => $profileImage, 'space' => $space));
        Yii::app()->clientScript->render($output);
        echo $output;
        Yii::app()->end();
    }


    /**
     * Handle the banner image upload
     */
    public function actionBannerImageUpload()
    {

        $space = $this->getSpace();
        $model = new UploadProfileImageForm();
        $json = array();

        $files = CUploadedFile::getInstancesByName('bannerfiles');
        $file = $files[0];
        $model->image = $file;

        if ($model->validate()) {

            $json['error'] = false;

            $profileImage = new ProfileBannerImage($space->guid);
            $profileImage->setNew($model->image);

            $json['name'] = "";
            $json['url'] = $profileImage->getUrl();
            $json['size'] = $model->image->getSize();
            $json['deleteUrl'] = "";
            $json['deleteType'] = "";
        } else {
            $json['error'] = true;
            $json['errors'] = $model->getErrors();
        }


        return $this->renderJson(array('files' => $json));
    }

    /**
     * Crops the banner image
     */
    public function actionCropBannerImage()
    {
        $space = $this->getSpace();

        $model = new CropProfileImageForm;
        $profileImage = new ProfileBannerImage($space->guid);

        if (isset($_POST['CropProfileImageForm'])) {
            $_POST['CropProfileImageForm'] = Yii::app()->input->stripClean($_POST['CropProfileImageForm']);
            $model->attributes = $_POST['CropProfileImageForm'];
            if ($model->validate()) {
                $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
                $this->htmlRedirect();
            }
        }

        $output = $this->renderPartial('cropBannerImage', array('model' => $model, 'profileImage' => $profileImage, 'space' => $space));
        Yii::app()->clientScript->render($output);
        echo $output;
        Yii::app()->end();
    }

    /**
     * Deletes the profile image or profile banner
     */
    public function actionDeleteProfileImage()
    {
        $this->forcePostRequest();

        $space = $this->getSpace();
        //$space->getProfileImage()->delete();

        $type = Yii::app()->request->getParam('type', 'profile');

        $json = array('type' => $type);

        $image = NULL;
        if ($type == 'profile') {
            $image = new ProfileImage($space->guid, 'default_space');
        } elseif ($type == 'banner') {
            $image = new ProfileBannerImage($space->guid);
        }

        if ($image) {
            $image->delete();
            $json['defaultUrl'] = $image->getUrl();
        }

        $this->renderJson($json);
    }

    /**
     * Modules Administration Action
     */
    public function actionModules()
    {
        $space = $this->getSpace();
        $this->render('modules', array('availableModules' => $this->getSpace()->getAvailableModules()));
    }

    public function actionEnableModule()
    {

        $this->forcePostRequest();

        $space = $this->getSpace();
        $moduleId = Yii::app()->request->getParam('moduleId', "");

        if (!$this->getSpace()->isModuleEnabled($moduleId)) {
            $this->getSpace()->enableModule($moduleId);
        }

        $this->redirect($this->createUrl('admin/modules', array('sguid' => $this->getSpace()->guid)));
    }

    public function actionDisableModule()
    {

        $this->forcePostRequest();

        $space = $this->getSpace();
        $moduleId = Yii::app()->request->getParam('moduleId', "");

        if ($space->isModuleEnabled($moduleId) && $space->canDisableModule($moduleId)) {
            $this->getSpace()->disableModule($moduleId);
        }

        $this->redirect($this->createUrl('admin/modules', array('sguid' => $this->getSpace()->guid)));
    }

    /**
     * Archives a workspace
     */
    public function actionArchive()
    {
        $this->forcePostRequest();
        $this->ownerOnly();
        $space = $this->getSpace();
        $space->archive();
        $this->htmlRedirect($this->createUrl('//space/admin/edit', array('sguid' => $space->guid)));
    }

    /**
     * UnArchives a workspace
     */
    public function actionUnArchive()
    {
        $this->forcePostRequest();
        $this->ownerOnly();
        $space = $this->getSpace();
        $space->unarchive();
        $this->htmlRedirect($this->createUrl('//space/admin/edit', array('sguid' => $space->guid)));
    }

    /**
     * Deletes this Space
     */
    public function actionDelete()
    {
        $this->ownerOnly();
        $space = $this->getSpace();
        $model = new SpaceDeleteForm;
        if (isset($_POST['SpaceDeleteForm'])) {
            $model->attributes = $_POST['SpaceDeleteForm'];

            if ($model->validate()) {
                $space->delete();
                $this->htmlRedirect($this->createUrl('//'));
            }
        }
        $this->render('delete', array('model' => $model, 'space' => $space));
    }

    /**
     * Request only allowed for workspace admins
     */
    public function adminOnly()
    {
        if (!$this->getSpace()->isAdmin())
            throw new CHttpException(403, 'Access denied - Space Administrator only!');
    }

    /**
     * Request only allowed for workspace owner
     */
    public function ownerOnly()
    {
        $workspace = $this->getSpace();

        if (!$workspace->isSpaceOwner() && !Yii::app()->user->isAdmin())
            throw new CHttpException(403, 'Access denied - Space Owner only!');
    }

}

?>
