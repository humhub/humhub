<?php

namespace humhub\core\space\controllers;

use Yii;
use \humhub\components\Controller;
use \yii\helpers\Url;
use \yii\web\HttpException;
use \humhub\core\user\models\User;
use humhub\core\space\models\Membership;

/**
 * AdminController provides all space administration actions.
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class AdminController extends \humhub\core\content\components\ContentContainerController
{

    /**
     * @var String Admin Sublayout
     */
    public $subLayout = "application.modules_core.space.views.space._layout";

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
        $this->redirect($this->contentContainer->createUrl('/space/admin/edit'));
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


        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('SpaceModule.controllers_AdminController', 'Saved'));
            return $this->redirect($model->createUrl('/space/admin/edit'));
        }

        return $this->render('edit', array('model' => $model));
    }

    /**
     * Members Administration Action
     */
    public function actionMembers()
    {
        $membersPerPage = 20;
        $space = $this->getSpace();

        // User Role Management
        if (isset($_POST['users'])) {
            $users = Yii::$app->request->post('users');

            // Loop over all users in Form
            foreach ($users as $userGuid) {
                // Get informations
                if (isset($_POST['user_' . $userGuid])) {
                    $userSettings = Yii::$app->request->post('user_' . $userGuid);

                    $user = User::findOne(['guid' => $userGuid]);
                    if ($user != null) {

                        // No changes on the Owner
                        if ($space->isSpaceOwner($user->id))
                            continue;

                        $membership = \humhub\core\space\models\Membership::findOne(['user_id' => $user->id, 'space_id' => $space->id]);
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
                $newOwnerId = Yii::$app->request->post('ownerId');

                if ($newOwnerId != $owner->id) {
                    if ($space->isMember($newOwnerId)) {
                        $space->setSpaceOwner($newOwnerId);

                        // Redirect to current space
                        return $this->redirect($space->createUrl('admin/members'));
                    }
                }
            }

            Yii::$app->getSession()->setFlash('data-saved', Yii::t('SpaceModule.controllers_AdminController', 'Saved'));
        } // Updated Users

        $query = $space->getMemberships();
        #$query = Membership::find();
        // Allow User Searches
        $search = Yii::$app->request->post('search');
        if ($search != "") {
            $query->joinWith('user');
            $query->andWhere('user.username LIKE :search OR user.email LIKE :search', [':search' => '%' . $search . '%']);
        }

        $countQuery = clone $query;
        $pagination = new \yii\data\Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $membersPerPage]);
        $query->offset($pagination->offset)->limit($pagination->limit);

        $invitedMembers = Membership::findAll(['space_id' => $space->id, 'status' => Membership::STATUS_INVITED]);

        $members = $query->all();

        return $this->render('members', array(
                    'space' => $space,
                    'pagination' => $pagination,
                    'members' => $members,
                    'invited_members' => $invitedMembers,
                    'search' => $search,
        ));
    }

    /**
     * User Manage Users Page, Reject Member Request Link
     */
    public function actionMembersRejectApplicant()
    {
        $this->forcePostRequest();

        $space = $this->getSpace();
        $userGuid = Yii::$app->request->get('userGuid');
        $user = User::findOne(['guid' => $userGuid]);

        if ($user != null) {
            $space->removeMember($user->id);
        }

        return $this->redirect($space->getUrl());
    }

    /**
     * User Manage Users Page, Approve Member Request Link
     */
    public function actionMembersApproveApplicant()
    {
        $this->forcePostRequest();

        $space = $this->getSpace();
        $userGuid = Yii::$app->request->get('userGuid');
        $user = User::findOne(['guid' => $userGuid]);

        if ($user != null) {
            $membership = $space->getMembership($user->id);
            if ($membership != null && $membership->status == Membership::STATUS_APPLICANT) {
                $space->addMember($user->id);
            }
        }

        return $this->redirect($space->getUrl());
    }

    /**
     * Removes a Member
     */
    public function actionRemoveMember()
    {
        $this->forcePostRequest();

        $workspace = $this->getSpace();
        $userGuid = Yii::$app->request->get('userGuid');
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
        \Yii::$app->response->format = 'json';

        $model = new \humhub\models\forms\UploadProfileImage();

        $json = array();

        $files = \yii\web\UploadedFile::getInstancesByName('spacefiles');
        $file = $files[0];
        $model->image = $file;

        if ($model->validate()) {

            $json['error'] = false;

            $profileImage = new \humhub\libs\ProfileImage($this->getSpace()->guid);
            $profileImage->setNew($model->image);

            $json['name'] = "";
            $json['url'] = $profileImage->getUrl();
            $json['size'] = $model->image->size;
            $json['deleteUrl'] = "";
            $json['deleteType'] = "";
        } else {
            $json['error'] = true;
            $json['errors'] = $model->getErrors();
        }

        return array('files' => $json);
    }

    /**
     * Crops the space image 
     */
    public function actionCropImage()
    {
        $space = $this->getSpace();
        $model = new \humhub\models\forms\CropProfileImage();
        $profileImage = new \humhub\libs\ProfileImage($space->guid);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
            return $this->htmlRedirect($space->getUrl());
        }


        return $this->renderAjax('cropImage', array('model' => $model, 'profileImage' => $profileImage, 'space' => $space));
    }

    /**
     * Handle the banner image upload
     */
    public function actionBannerImageUpload()
    {
        \Yii::$app->response->format = 'json';

        $model = new \humhub\models\forms\UploadProfileImage();
        $json = array();

        $files = \yii\web\UploadedFile::getInstancesByName('bannerfiles');
        $file = $files[0];
        $model->image = $file;

        if ($model->validate()) {
            $profileImage = new \humhub\libs\ProfileBannerImage($this->getSpace()->guid);
            $profileImage->setNew($model->image);

            $json['error'] = false;
            $json['name'] = "";
            $json['url'] = $profileImage->getUrl();
            $json['size'] = $model->image->size;
            $json['deleteUrl'] = "";
            $json['deleteType'] = "";
        } else {
            $json['error'] = true;
            $json['errors'] = $model->getErrors();
        }

        return ['files' => $json];
    }

    /**
     * Crops the banner image
     */
    public function actionCropBannerImage()
    {
        $space = $this->getSpace();
        $model = new \humhub\models\forms\CropProfileImage();
        $profileImage = new \humhub\libs\ProfileBannerImage($space->guid);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
            return $this->htmlRedirect($space->getUrl());
        }

        return $this->renderAjax('cropBannerImage', array('model' => $model, 'profileImage' => $profileImage, 'space' => $space));
    }

    /**
     * Deletes the profile image or profile banner
     */
    public function actionDeleteProfileImage()
    {
        \Yii::$app->response->format = 'json';
        $this->forcePostRequest();

        $space = $this->getSpace();

        $type = Yii::$app->request->get('type', 'profile');
        $json = array('type' => $type);

        $image = NULL;
        if ($type == 'profile') {
            $image = new \humhub\libs\ProfileImage($space->guid, 'default_space');
        } elseif ($type == 'banner') {
            $image = new \humhub\libs\ProfileBannerImage($space->guid);
        }

        if ($image) {
            $image->delete();
            $json['defaultUrl'] = $image->getUrl();
        }

        return $json;
    }

    /**
     * Modules Administration Action
     */
    public function actionModules()
    {
        $space = $this->getSpace();
        return $this->render('modules', ['availableModules' => $space->getAvailableModules(), 'space' => $space]);
    }

    public function actionEnableModule()
    {
        $this->forcePostRequest();

        $space = $this->getSpace();
        $moduleId = Yii::$app->request->get('moduleId', "");

        if (!$space->isModuleEnabled($moduleId)) {
            $space->enableModule($moduleId);
        }

        return $this->redirect($space->createUrl('/space/admin/modules'));
    }

    public function actionDisableModule()
    {

        $this->forcePostRequest();

        $space = $this->getSpace();
        $moduleId = Yii::$app->request->get('moduleId', "");

        if ($space->isModuleEnabled($moduleId) && $space->canDisableModule($moduleId)) {
            $space->disableModule($moduleId);
        }

        return $this->redirect($space->createUrl('/space/admin/modules'));
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

        if (!$workspace->isSpaceOwner() && !Yii::$app->user->isAdmin())
            throw new CHttpException(403, 'Access denied - Space Owner only!');
    }

}

?>
