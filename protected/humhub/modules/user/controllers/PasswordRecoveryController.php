<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\forms\AccountRecoverPassword;
use humhub\modules\user\Module as UserModule;
use humhub\modules\user\services\PasswordRecoveryService;
use Yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * Password Recovery
 *
 * @since 1.1
 */
class PasswordRecoveryController extends Controller
{

    /**
     * @inheritdoc
     */
    public $layout = "@humhub/modules/user/views/layouts/main";

    /**
     * Allow guest access independently from guest mode setting.
     *
     * @var string
     */
    public $access = ControllerAccess::class;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
            ]
        ];
    }

    public function beforeAction($action)
    {
        /** @var UserModule $userModule */
        $userModule = Yii::$app->getModule('user');

        if (!$userModule->passwordRecoveryRoute) {
            throw new NotFoundHttpException();
        }

        return parent::beforeAction($action);
    }

    /**
     * Recover Password Action
     * Generates an password reset token and sends an e-mail to the user.
     */
    public function actionIndex()
    {
        $model = new AccountRecoverPassword();

        if ($model->load(Yii::$app->request->post()) && $model->recover()) {
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('success_modal', ['model' => $model]);
            }
            return $this->render('success', ['model' => $model]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('index_modal', ['model' => $model]);
        }
        return $this->render('index', ['model' => $model]);
    }

    /**
     * Resets users password based on given token
     * @throws HttpException
     */
    public function actionReset()
    {
        $user = User::findOne(['guid' => Yii::$app->request->get('guid')]);

        $model = new Password();
        $passwordRecoveryService = new PasswordRecoveryService($user);

        if ($passwordRecoveryService->reset($model, Yii::$app->request->get('token'))) {
            return $this->render('reset_success');
        }

        return $this->render('reset', ['model' => $model]);
    }

}
