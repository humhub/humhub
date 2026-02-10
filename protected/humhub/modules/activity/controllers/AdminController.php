<?php

namespace humhub\modules\activity\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\activity\models\MailSummaryForm;
use Yii;

/**
 * AdminController is for system administrators to set activity e-mail defaults.
 *
 * @since 1.2
 */
class AdminController extends Controller
{
    protected function getAccessRules()
    {
        return [
            ['permissions' => ManageSettings::class],
            ['permissions' => [ManageUsers::class], 'actions' => ['reset-all-users']],
        ];
    }

    public function actionDefaults()
    {
        $this->subLayout = '@admin/views/layouts/setting';
        $model = new MailSummaryForm();

        $model->loadCurrent();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
        }

        return $this->render('defaults', [
            'model' => $model,
        ]);
    }

    public function actionResetAllUsers()
    {
        $this->forcePostRequest();
        $model = new MailSummaryForm();
        $model->resetAllUserSettings();

        $this->view->saved();
        $this->redirect(['defaults']);
    }

}
