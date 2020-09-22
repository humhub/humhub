<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\components\Response;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\forms\AddGroupMemberForm;
use humhub\modules\admin\models\GroupSearch;
use humhub\modules\admin\models\UserSearch;
use humhub\modules\admin\notifications\ExcludeGroupNotification;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\user\models\forms\EditGroupForm;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\User;
use humhub\modules\user\models\UserPicker;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\web\HttpException;

/**
 * Group Administration Controller
 *
 * @since 0.5
 */
class GroupController extends Controller
{

    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    public function init()
    {
        $this->subLayout = '@admin/views/layouts/user';
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Groups'));

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => ManageGroups::class],
        ];
    }

    /**
     * List all available user groups
     */
    public function actionIndex()
    {
        $searchModel = new GroupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Edits or Creates a user group
     */
    public function actionEdit()
    {

        // Create Group Edit Form
        $group = EditGroupForm::findOne(['id' => Yii::$app->request->get('id')]);

        if (!$group) {
            $group = new EditGroupForm();
        }

        $this->checkGroupAccess($group);

        $wasNew = $group->isNewRecord;

        if ($group->load(Yii::$app->request->post()) && $group->validate() && $group->save()) {
            $this->view->saved();
            if ($wasNew) {
                return $this->redirect([
                    '/admin/group/manage-group-users',
                    'id' => $group->id,
                ]);
            }
        }

        return $this->render('edit', [
            'group' => $group,
            'showDeleteButton' => (!$group->isNewRecord && !$group->is_admin_group),
            'isCreateForm' => $group->isNewRecord,
            'isManagerApprovalSetting' => Yii::$app->getModule('user')->settings->get('auth.needApproval'),
        ]);
    }

    public function actionManagePermissions()
    {
        $group = Group::findOne(['id' => Yii::$app->request->get('id')]);

        $this->checkGroupAccess($group);

        // Save changed permission states
        if (!$group->isNewRecord && Yii::$app->request->post('dropDownColumnSubmit')) {
            $permission = Yii::$app->user->permissionManager->getById(Yii::$app->request->post('permissionId'), Yii::$app->request->post('moduleId'));
            if ($permission === null) {
                throw new HttpException(500, 'Could not find permission!');
            }
            Yii::$app->user->permissionManager->setGroupState($group->id, $permission, Yii::$app->request->post('state'));

            return $this->asJson([]);
        }

        return $this->render('permissions', ['group' => $group]);
    }

    public function actionManageGroupUsers()
    {
        $group = Group::findOne(['id' => Yii::$app->request->get('id')]);
        $this->checkGroupAccess($group);

        $searchModel = new UserSearch();
        $searchModel->query = $group->getUsers();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('members', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'group' => $group,
            'addGroupMemberForm' => new AddGroupMemberForm(),
            'isManagerApprovalSetting' => Yii::$app->getModule('user')->settings->get('auth.needApproval'),
        ]);
    }

    public function actionRemoveGroupUser()
    {
        $this->forcePostRequest();
        $request = Yii::$app->request;
        $group = Group::findOne(['id' => $request->get('id')]);
        $this->checkGroupAccess($group);

        if ($group->removeUser($request->get('userId'))) {
            ExcludeGroupNotification::instance()
                ->about($group)
                ->from(Yii::$app->user->identity)
                ->send(User::findOne(['id' => $request->get('userId')]));
        }

        if ($request->isAjax) {
            Yii::$app->response->format = 'json';
            return ['success' => true];
        }

        return $this->redirect([
            '/admin/group/manage-group-users',
            'id' => $group->id,
        ]);
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

        $this->checkGroupAccess($group);

        //Double check to get sure we don't remove the admin group
        if (!$group->is_admin_group) {
            $group->delete();
        }

        return $this->redirect(['/admin/group']);
    }

    public function actionEditManagerRole()
    {
        $this->forcePostRequest();

        $group = Group::findOne(Yii::$app->request->post('id'));
        $this->checkGroupAccess($group);

        $value = Yii::$app->request->post('value');

        if ($value === null) {
            throw new HttpException(400,
                Yii::t('AdminModule.user', 'No value found!')
            );
        }

        $groupUser = $group->getGroupUser(User::findOne(Yii::$app->request->post('userId')));
        if ($groupUser === null) {
            throw new HttpException(404,
                Yii::t('AdminModule.user', 'Group user not found!')
            );
        }

        $groupUser->is_group_manager = (bool)$value;

        return $this->asJson(['success' => $groupUser->save()]);
    }

    public function actionAddMembers()
    {
        $this->forcePostRequest();
        $form = new AddGroupMemberForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $form->save();
        }

        return $this->redirect([
            '/admin/group/manage-group-users',
            'id' => $form->groupId,
        ]);
    }

    public function actionNewMemberSearch()
    {
        Yii::$app->response->format = 'json';

        $keyword = Yii::$app->request->get('keyword');
        $group = Group::findOne(Yii::$app->request->get('id'));



        $subQuery = (new Query())->select('*')->from(GroupUser::tableName() . ' g')->where([
            'and',
            'g.user_id=user.id',
            ['g.group_id' => $group->id],
        ]);

        $query = User::find()->where(['not exists', $subQuery]);

        $result = UserPicker::filter([
            'keyword' => $keyword,
            'query' => $query,
            'fillUser' => true,
            'fillUserQuery' => $group->getUsers(),
            'disabledText' => Yii::t('AdminModule.user',
                'User is already a member of this group.'),
        ]);

        return $result;
    }

    public function actionAdminUserSearch($keyword, $id)
    {
        $group = Group::findOne($id);

        if(!$group) {
            throw new HttpException(404, Yii::t('AdminModule.user', 'Group not found!'));
        }

        return $this->asJson(UserPicker::filter([
            'query' => $group->getUsers(),
            'keyword' => $keyword,
            'fillQuery' => User::find(),
            'disableFillUser' => false,
        ]));
    }

    public function checkGroupAccess($group)
    {
        if(!$group) {
            throw new HttpException(404, Yii::t('AdminModule.user', 'Group not found!'));
        }

        if($group->is_admin_group && !Yii::$app->user->isAdmin()) {
            throw new HttpException(403);
        }
    }

}
