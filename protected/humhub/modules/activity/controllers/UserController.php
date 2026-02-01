<?php

namespace humhub\modules\activity\controllers;

use humhub\modules\activity\models\MailSummaryForm;
use humhub\modules\activity\Module;
use humhub\modules\user\components\BaseAccountController;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * UserController allows users to modify the E-Mail summary settings.
 *
 * @property-read Module $module
 * @since 1.2
 */
class UserController extends BaseAccountController
{
    public function actionIndex()
    {
        if (!$this->module->enableMailSummaries) {
            throw new NotFoundHttpException('Mail summaries are not enabled.');
        }

        $model = new MailSummaryForm();
        $model->user = $this->getUser();
        $model->loadCurrent();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
        }

        return $this->render('defaults', [
            'model' => $model,
        ]);
    }

    /**
     * Resets the overwritten user settings to the system defaults
     */
    public function actionReset()
    {
        $this->forcePostRequest();
        $model = new MailSummaryForm();
        $model->user = $this->getUser();
        $model->resetUserSettings();

        return $this->redirect(['index']);
    }

}
