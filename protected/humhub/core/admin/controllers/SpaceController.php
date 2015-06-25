<?php

namespace humhub\core\admin\controllers;

use Yii;
use yii\helpers\Url;
use humhub\components\Controller;
use humhub\models\Setting;

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class SpaceController extends Controller
{

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
     * Shows all available spaces
     */
    public function actionIndex()
    {
        $searchModel = new \humhub\core\admin\models\SpaceSearch();
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
        $form = new \humhub\core\admin\models\forms\SpaceSettingsForm;
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

}
