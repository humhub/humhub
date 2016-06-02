<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\user\models\Group;
use humhub\modules\user\widgets\UserPicker;
use humhub\modules\user\models\User;
use humhub\modules\admin\models\forms\AddGroupMemberForm;

/**
 * Group Administration Controller
 *
 * @since 0.5
 */
class GroupController extends Controller
{

    public function init()
    {
        $this->subLayout = '@admin/views/layouts/user';
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Groups'));
        return parent::init();
    }

    /**
     * List all available user groups
     */
    public function actionIndex()
    {
        $searchModel = new \humhub\modules\admin\models\GroupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', array(
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel
        ));
    }

    /**
     * Edits or Creates a user group
     */
    public function actionEdit()
    {

        // Create Group Edit Form
        $group = Group::findOne(['id' => Yii::$app->request->get('id')]);
        if ($group === null) {
            $group = new Group();
        }

        $group->scenario = Group::SCENARIO_EDIT;
        $group->populateDefaultSpaceGuid();
        $group->populateManagerGuids();

        if ($group->load(Yii::$app->request->post()) && $group->validate()) {
            $group->save();
            return $this->redirect(['/admin/group/manage-group-users', 'id' => $group->id]);
        }

        return $this->render('edit', [
                    'group' => $group,
                    'showDeleteButton' => (!$group->isNewRecord && !$group->is_admin_group),
                    'isCreateForm' => $group->isNewRecord,
                    'isManagerApprovalSetting' => Yii::$app->getModule('user')->settings->get('auth.needApproval')
        ]);
    }

    public function actionManagePermissions()
    {
        $group = Group::findOne(['id' => Yii::$app->request->get('id')]);

        // Save changed permission states
        if (!$group->isNewRecord && Yii::$app->request->post('dropDownColumnSubmit')) {
            Yii::$app->response->format = 'json';
            $permission = Yii::$app->user->permissionManager->getById(Yii::$app->request->post('permissionId'), Yii::$app->request->post('moduleId'));
            if ($permission === null) {
                throw new \yii\web\HttpException(500, 'Could not find permission!');
            }
            Yii::$app->user->permissionManager->setGroupState($group->id, $permission, Yii::$app->request->post('state'));
            return [];
        }

        return $this->render('permissions', [
                    'group' => $group
        ]);
    }

    public function actionManageGroupUsers()
    {
        $group = Group::findOne(['id' => Yii::$app->request->get('id')]);
        $searchModel = new \humhub\modules\admin\models\UserSearch();
        $searchModel->query = $group->getUsers();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('members', [
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel,
                    'group' => $group,
                    'addGroupMemberForm' => new AddGroupMemberForm(),
                    'isManagerApprovalSetting' => Yii::$app->getModule('user')->settings->get('auth.needApproval')
        ]);
    }

    public function actionRemoveGroupUser()
    {
        $this->forcePostRequest();
        $group = Group::findOne(['id' => Yii::$app->request->get('id')]);
        $group->removeUser(Yii::$app->request->get('userId'));
        return $this->redirect(['/admin/group/manage-group-users', 'id' => $group->id]);
    }

    /**
     * Deletes a group
     *
     * On deletion all group members will be moved to another group.
     */
    public function actionDelete()
    {
        $this->forcePostRequest();
        $group = Group::findOne(['id' => Yii::$app->request->get('id')]);

        if ($group == null) {
            throw new \yii\web\HttpException(404, Yii::t('AdminModule.controllers_GroupController', 'Group not found!'));
        }

        //Double check to get sure we don't remove the admin group
        if (!$group->is_admin_group) {
            $group->delete();
        }

        return $this->redirect(['/admin/group']);
    }

    public function actionEditManagerRole()
    {
        Yii::$app->response->format = 'json';
        $this->forcePostRequest();
        $group = Group::findOne(Yii::$app->request->post('id'));
        $value = Yii::$app->request->post('value');

        if ($group == null) {
            throw new \yii\web\HttpException(404, Yii::t('AdminModule.controllers_GroupController', 'Group not found!'));
        } else if ($value == null) {
            throw new \yii\web\HttpException(400, Yii::t('AdminModule.controllers_GroupController', 'No value found!'));
        }

        $groupUser = $group->getGroupUser(User::findOne(Yii::$app->request->post('userId')));

        if ($groupUser == null) {
            throw new \yii\web\HttpException(404, Yii::t('AdminModule.controllers_GroupController', 'Group user not found!'));
        }


        $groupUser->is_group_manager = ($value) ? true : false;
        $groupUser->save();

        return [];
    }

    public function actionAddMembers()
    {
        $this->forcePostRequest();
        $form = new AddGroupMemberForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $form->save();
        }
        return $this->redirect(['/admin/group/manage-group-users', 'id' => $form->groupId]);
    }

    public function actionNewMemberSearch()
    {
        Yii::$app->response->format = 'json';

        $keyword = Yii::$app->request->get('keyword');
        $group = Group::findOne(Yii::$app->request->get('id'));

        $result = UserPicker::filter([
                    'keyword' => $keyword,
                    'query' => User::find()
        ]);

        $i = 0;
        foreach ($result as $jsonUser) {
            if ($group->isMember($jsonUser['id'])) {
                $result[$i]['disabled'] = true;
            }
            $i++;
        }
        return $result;
    }

    public function actionAdminUserSearch()
    {
        Yii::$app->response->format = 'json';

        $keyword = Yii::$app->request->get('keyword');
        $group = Group::findOne(Yii::$app->request->get('id'));

        return UserPicker::filter([
                    'query' => $group->getUsers(),
                    'keyword' => $keyword,
                    'fillQuery' => User::find(),
                    'disableFillUser' => false
        ]);
    }

}
