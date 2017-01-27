<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use Yii;
use yii\web\HttpException;
use humhub\components\Controller;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\forms\AccountRecoverPassword;

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

    /**
     * Recover Password Action
     * Generates an password reset token and sends an e-mail to the user.
     */
    public function actionIndex()
    {
        $model = new AccountRecoverPassword();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->recover()) {
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('success_modal', array('model' => $model));
            }
            return $this->render('success', array('model' => $model));
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('index_modal', array('model' => $model));
        }
        return $this->render('index', array('model' => $model));
    }

    /**
     * Resets users password based on given token
     */
    public function actionReset()
    {
        $user = User::findOne(array('guid' => Yii::$app->request->get('guid')));

        if ($user === null || !$this->checkPasswordResetToken($user, Yii::$app->request->get('token'))) {
            throw new HttpException('500', 'It looks like you clicked on an invalid password reset link. Please try again.');
        }

        $model = new Password();
        $model->scenario = 'registration';

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->getModule('user')->settings->contentContainer($user)->delete('passwordRecoveryToken');
            $model->user_id = $user->id;
            $model->setPassword($model->newPassword);
            $model->save();
            return $this->render('reset_success');
        }

        return $this->render('reset', array('model' => $model));
    }

    private function checkPasswordResetToken($user, $token)
    {
        // Saved token - Format: randomToken.generationTime
        $savedTokenInfo = Yii::$app->getModule('user')->settings->contentContainer($user)->get('passwordRecoveryToken');

        if ($savedTokenInfo) {
            list($generatedToken, $generationTime) = explode('.', $savedTokenInfo);
            if (\humhub\libs\Helpers::same($generatedToken, $token)) {
                // Check token generation time
                if ($generationTime + (24 * 60 * 60) >= time()) {
                    return true;
                }
            }
        }

        return false;
    }

}

?>
