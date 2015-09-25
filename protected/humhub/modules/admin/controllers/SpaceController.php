<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\HttpException;
use humhub\modules\admin\components\Controller;
use humhub\models\Setting;
use humhub\modules\space\models\Type;
use humhub\modules\space\models\Space;

/**
 * SpaceController provides global space administration.
 * 
 * @since 0.5
 */
class SpaceController extends Controller
{

    /**
     * Shows all available spaces
     */
    public function actionIndex()
    {
        $searchModel = new \humhub\modules\admin\models\SpaceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', array(
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel
        ));
    }

    /**
     * General Space Settings
     */
    public function actionSettings()
    {
        $form = new \humhub\modules\admin\models\forms\SpaceSettingsForm;
        $form->defaultJoinPolicy = Setting::Get('defaultJoinPolicy', 'space');
        $form->defaultVisibility = Setting::Get('defaultVisibility', 'space');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Setting::Set('defaultJoinPolicy', $form->defaultJoinPolicy, 'space');
            Setting::Set('defaultVisibility', $form->defaultVisibility, 'space');

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SpaceController', 'Saved'));
            $this->redirect(Url::toRoute('settings'));
        }

        return $this->render('settings', array('model' => $form));
    }

    /**
     * Lists all created space types
     */
    public function actionListTypes()
    {
        $searchModel = new \humhub\modules\admin\models\SpaceTypeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('listTypes', array(
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel
        ));
    }

    /**
     * Edit/Create a space type
     */
    public function actionEditType()
    {
        $type = Type::findOne(['id' => Yii::$app->request->get('id')]);
        if ($type === null) {
            $type = new Type();
        }

        if ($type->load(Yii::$app->request->post()) && $type->validate()) {
            $type->save();
            return $this->redirect(['list-types']);
        }

        return $this->render('editType', array(
                    'type' => $type,
                    'canDelete' => $this->canDeleteSpaceType()
        ));
    }

    /**
     * Delete a space type
     */
    public function actionDeleteType()
    {
        if (!$this->canDeleteSpaceType()) {
            throw new HttpException(500, 'Could not delete space type!');
        }

        $type = Type::findOne(['id' => Yii::$app->request->get('id')]);
        if ($type === null) {
            throw new \yii\web\HttpException(404, 'Could not find space type!');
        }

        $model = new \humhub\modules\admin\models\forms\SpaceTypeDelete();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            foreach (Space::find()->where(['space_type_id' => $type->id])->all() as $space) {
                $space->space_type_id = $model->space_type_id;
                $space->save();
            }
            $type->delete();
            return $this->redirect(['list-types']);
        }

        $alternativeTypes = \yii\helpers\ArrayHelper::map(Type::find()->where(['!=', 'id', $type->id])->all(), 'id', 'title');

        return $this->render('deleteType', array(
                    'type' => $type,
                    'model' => $model,
                    'alternativeTypes' => $alternativeTypes
        ));
    }

    /**
     * Checks if a space type can deleted
     * 
     * @return boolean
     */
    private function canDeleteSpaceType()
    {
        return (Type::find()->count() > 1);
    }

}
