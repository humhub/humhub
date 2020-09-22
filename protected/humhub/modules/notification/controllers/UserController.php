<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers;

use Yii;
use humhub\modules\user\components\BaseAccountController;
use humhub\modules\notification\models\forms\NotificationSettings;

/**
 * UserController allows users to modify the Notification settings.
 *
 * @since 1.2
 * @author buddha
 */
class UserController extends BaseAccountController
{

    public function actionIndex()
    {
        $form = new NotificationSettings(['user' => Yii::$app->user->getIdentity()]);

        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            $this->view->saved();
        }

        return $this->render('notification', ['model' => $form]);
    }

    /**
     * Resets the overwritten user settings to the system defaults
     */
    public function actionReset()
    {
        $this->forcePostRequest();
        $model = new NotificationSettings(['user' => $this->getUser()]);
        $model->resetUserSettings();
        $this->view->saved();
        $this->redirect(['index']);
    }
}
