<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageSettings;

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
    public $adminOnly = false;

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
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => [
                ManageSpaces::className(),
                ManageSettings::className()
            ]],
        ];
    }

    /**
     * Shows all available spaces
     */
    public function actionIndex()
    {
        if (Yii::$app->user->can(new ManageSpaces())) {
            $searchModel = new \humhub\modules\admin\models\SpaceSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('index', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel
            ]);
        } else if (Yii::$app->user->can(new ManageSettings())) {
            $this->redirect([
                'settings'
            ]);
        }
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
            $this->view->saved();
            return $this->redirect([
                'settings'
            ]);
        }

        return $this->render('settings', [
            'model' => $form]
        );
    }

}
