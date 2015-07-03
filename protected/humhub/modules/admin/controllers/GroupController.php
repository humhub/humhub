<?php

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use yii\helpers\Url;

/**
 * Group Administration Controller
 *
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class GroupController extends Controller
{

    /**
     * Layout View to use
     *
     * @var type
     */
    public $subLayout = "/_layout";

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'adminOnly' => true
            ]
        ];
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

        $group->scenario = 'edit';
        $group->populateDefaultSpaceGuid();
        $group->populateAdminGuids();

        if ($group->load(Yii::$app->request->post()) && $group->validate()) {
            $group->save();
            $this->redirect(Url::toRoute('/admin/group'));
        }

        $showDeleteButton = (!$group->isNewRecord && Group::find()->count() > 1);
        return $this->render('edit', ['group' => $group, 'showDeleteButton' => $showDeleteButton]);
    }

    /**
     * Deletes a group
     *
     * On deletion all group members will be moved to another group.
     */
    public function actionDelete()
    {
        $group = Group::findOne(['id' => Yii::$app->request->get('id')]);
        if ($group == null)
            throw new \yii\web\HttpException(404, Yii::t('AdminModule.controllers_GroupController', 'Group not found!'));

        $model = new \humhub\modules\admin\models\forms\AdminDeleteGroupForm;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            foreach (User::findAll(['group_id' => $group->id]) as $user) {
                $user->group_id = $model->group_id;
                $user->save();
            }
            $group->delete();
            $this->redirect(Url::toRoute("/admin/group"));
        }

        $alternativeGroups = \yii\helpers\ArrayHelper::map(Group::find()->where('id != :id', array(':id' => $group->id))->all(), 'id', 'name');
        return $this->render('delete', array('group' => $group, 'model' => $model, 'alternativeGroups' => $alternativeGroups));
    }

}
