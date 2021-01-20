<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\Controller;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\User;
use Yii;

/**
 * Must Change Password
 *
 * @since 1.8
 */
class MustChangePasswordController extends Controller
{

    /**
     * @inheritdoc
     */
    public $layout = "@humhub/modules/user/views/layouts/main";

    /**
     * Must Change Password Form Action
     * Display a form to force user to change password.
     */
    public function actionIndex()
    {
        Yii::$app->getModule('live')->isActive = false;

        if (!Yii::$app->user->mustChangePassword()) {
            return $this->goHome();
        }

        if (!($model = Password::findOne(['user_id' => Yii::$app->user->id]))) {
            $model = new Password();
            $model->user_id = Yii::$app->user->id;
        }
        $model->scenario = 'changePassword';

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->setPassword($model->newPassword);
            if ($model->save()) {
                /* @var User $user */
                if ($user = Yii::$app->user->getIdentity()) {
                    $user->setMustChangePassword(false);
                }
                $this->view->success(Yii::t('UserModule.base', 'Password changed'));
                return $this->goHome();
            }
        }

        return $this->render('index', ['model' => $model]);
    }

}
