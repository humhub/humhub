<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\modules\admin\components\Controller;

/**
 * SpaceController provides global space administration.
 *
 * @since 0.5
 */
class SpaceController extends Controller
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->subLayout = '@admin/views/layouts/space';
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Spaces'));
        return parent::init();
    }

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
        $form->defaultJoinPolicy = Yii::$app->getModule('space')->settings->get('defaultJoinPolicy');
        $form->defaultVisibility = Yii::$app->getModule('space')->settings->get('defaultVisibility');
        $form->defaultContentVisibility = Yii::$app->getModule('space')->settings->get('defaultContentVisibility');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Yii::$app->getModule('space')->settings->set('defaultJoinPolicy', $form->defaultJoinPolicy);
            Yii::$app->getModule('space')->settings->set('defaultVisibility', $form->defaultVisibility);
            Yii::$app->getModule('space')->settings->set('defaultContentVisibility', $form->defaultContentVisibility);

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SpaceController', 'Saved'));
            return $this->redirect(['settings']);
        }

        return $this->render('settings', array('model' => $form));
    }

}
