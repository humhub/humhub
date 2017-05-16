<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use Yii;
use yii\web\HttpException;
use yii\authclient\ClientInterface;
use humhub\components\Controller;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\forms\Registration;
use humhub\modules\user\authclient\interfaces\ApprovalBypass;

/**
 * RegistrationController handles new user registration 
 * 
 * @since 1.1
 */
class RegistrationController extends Controller
{

    /**
     * @inheritdoc
     */
    public $layout = "@humhub/modules/user/views/layouts/main";

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!Yii::$app->user->isGuest) {
            throw new HttpException(401, 'Your are already logged in! - Logout first!');
        }

        return parent::beforeAction($action);
    }

    /**
     * Registration Form
     * 
     * @return type
     * @throws HttpException
     */
    public function actionIndex()
    {
        $registration = new Registration();

        /**
         * @var \yii\authclient\BaseClient
         */
        $authClient = null;
        $inviteToken = Yii::$app->request->get('token', '');

        if ($inviteToken != '') {
            $this->handleInviteRegistration($inviteToken, $registration);
        } elseif (Yii::$app->session->has('authClient')) {
            $authClient = Yii::$app->session->get('authClient');
            $this->handleAuthClientRegistration($authClient, $registration);
        } else {
            Yii::$app->session->setFlash('error', 'Registration failed.');
            return $this->redirect(['/user/auth/login']);
        }

        if ($registration->submitted('save') && $registration->validate() && $registration->register($authClient)) {
            Yii::$app->session->remove('authClient');

            // Autologin when user is enabled (no approval required)
            if ($registration->getUser()->status === User::STATUS_ENABLED) {
                Yii::$app->user->switchIdentity($registration->models['User']);
                $registration->models['User']->updateAttributes(['last_login' => new \yii\db\Expression('NOW()')]);
                return $this->redirect(['/dashboard/dashboard']);
            }

            return $this->render('success', [
                        'form' => $registration,
                        'needApproval' => ($registration->getUser()->status === User::STATUS_NEED_APPROVAL)
            ]);
        }

        return $this->render('index', ['hForm' => $registration]);
    }

    protected function handleInviteRegistration($inviteToken, Registration $form)
    {
        $userInvite = Invite::findOne(['token' => $inviteToken]);
        if (!$userInvite) {
            throw new HttpException(404, 'Invalid registration token!');
        }
        if ($userInvite->language) {
            Yii::$app->language = $userInvite->language;
        }
        $form->getUser()->email = $userInvite->email;
    }

    /**
     * @param \yii\authclient\BaseClient $authClient
     * @param Registration $registration
     * @return boolean already all registration data gathered
     * @throws Exception
     */
    protected function handleAuthClientRegistration(ClientInterface $authClient, Registration $registration)
    {
        $attributes = $authClient->getUserAttributes();

        if (!isset($attributes['id'])) {
            throw new Exception("No user id given by authclient!");
        }

        $registration->enablePasswordForm = false;
        if ($authClient instanceof ApprovalBypass) {
            $registration->enableUserApproval = false;
        }

        // do not store id attribute
        unset($attributes['id']);

        $registration->getUser()->setAttributes($attributes, false);
        $registration->getProfile()->setAttributes($attributes, false);
    }

}

?>
