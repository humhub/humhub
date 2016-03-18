<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use yii\helpers\Url;
use humhub\modules\admin\components\Controller;
use humhub\modules\user\models\Group;
use humhub\modules\user\widgets\UserPicker;
use humhub\modules\user\models\User;

/**
 * Group Administration Controller
 *
 * @since 0.5
 */
class GroupController extends Controller
{

    public function init() {
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
        $group->populateAdminGuids();
         
        
        
        if ($group->load(Yii::$app->request->post()) && $group->validate()) {
            $group->save();
            $this->redirect(Url::toRoute('/admin/group'));
        }

        $showDeleteButton = (!$group->isNewRecord && !$group->is_admin_group);

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

        return $this->render('edit', [
                    'group' => $group,
                    'showDeleteButton' => $showDeleteButton,
        ]);
    }

    /**
     * Deletes a group
     *
     * On deletion all group members will be moved to another group.
     */
    public function actionDelete()
    {
        $group = Group::findOne(['id' => Yii::$app->request->get('id')]);
        
        if ($group == null) {
            throw new \yii\web\HttpException(404, Yii::t('AdminModule.controllers_GroupController', 'Group not found!'));
        } 
        
        //Double check to get sure we don't remove the admin group
        if(!$group->is_admin_group) {
            $group->delete();
        }
        
        $this->redirect(Url::toRoute("/admin/group"));
    }
    
    public function actionAdminUserSearch()
    {
        Yii::$app->response->format = 'json';
        
        $keyword = Yii::$app->request->get('keyword');
        $group = Group::findOne(Yii::$app->request->get('id'));
        
        return UserPicker::filter([
            'query' => $group->getUsers(),
            'keyword' => $keyword,
            'fillQuery' => User::find()
        ]);
    }

}
