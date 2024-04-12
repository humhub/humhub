<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\controllers;

use humhub\modules\activity\models\MailSummaryForm;
use humhub\modules\activity\Module;
use humhub\modules\user\components\BaseAccountController;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * UserController allows users to modify the E-Mail summary settings.
 *
 * @since 1.2
 * @author Luke
 */
class UserController extends BaseAccountController
{
    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('activity');
        if (!$module->enableMailSummaries) {
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

        $this->redirect(['index']);
    }

}
