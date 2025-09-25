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
use Yii;
use yii\web\ForbiddenHttpException;
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
            ],
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
     * @return string
     * @throws HttpException
     */
    public function actionReset()
    {
        $user = User::findOne(['guid' => Yii::$app->request->get('guid')]);

        if ($user === null || !$user->getPasswordRecoveryService()->checkToken(Yii::$app->request->get('token'))) {
            throw new NotFoundHttpException(Yii::t('UserModule.base', 'It looks like you clicked on an invalid password reset link. Please try again.'));
        }

        // Checks if we can recover users password.
        // This may not possible on e.g. LDAP accounts.
        $passwordAuth = new \humhub\modules\user\authclient\Password();
        if ($user->auth_mode !== $passwordAuth->getId()) {
            throw new ForbiddenHttpException(Yii::t('UserModule.account', 'Password recovery disabled. Please contact your system administrator.'));
        }

        $model = new Password();

        if ($model->load(Yii::$app->request->post())
            && $user->getPasswordRecoveryService()->reset($model)) {
            return $this->render('reset_success');
        }

        return $this->render('reset', ['model' => $model]);
    }

}
