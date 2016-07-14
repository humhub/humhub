<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\HttpException;
use humhub\modules\admin\components\Controller;
use humhub\models\Setting;
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
        $form->defaultContentVisibility = Setting::Get('defaultContentVisibility', 'space');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Setting::Set('defaultJoinPolicy', $form->defaultJoinPolicy, 'space');
            Setting::Set('defaultVisibility', $form->defaultVisibility, 'space');
            Setting::Set('defaultContentVisibility', $form->defaultContentVisibility, 'space');

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SpaceController', 'Saved'));
            return $this->redirect(Url::toRoute('settings'));
        }

        return $this->render('settings', array('model' => $form));
    }

}
