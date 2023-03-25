<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\Module;
use humhub\modules\user\widgets\AuthChoice;
use Yii;
use yii\base\Exception;
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
 * @property Module $module
 * @since 1.1
 */
class RegistrationController extends Controller
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
     * @throws HttpException
     */
    public function beforeAction($action)
    {
        if (!Yii::$app->user->isGuest) {
            throw new HttpException(401, Yii::t('UserModule.base', 'Your are already logged in! - Logout first!'));
        }

        return parent::beforeAction($action);
    }

    /**
     * Registration Form
     *
     * @throws HttpException
     * @throws Exception
     */
    public function actionIndex()
    {
        $registration = new Registration();

        /**
         * @var \yii\authclient\BaseClient
         */
        $authClient = null;
        $inviteToken = Yii::$app->request->get('token', '');
        $showAuthClients = AuthChoice::hasClients();

        if ($inviteToken != '') {
            $this->handleInviteRegistration($inviteToken, $registration);
        } elseif (Yii::$app->session->has('authClient')) {
            $authClient = Yii::$app->session->get('authClient');
            $this->handleAuthClientRegistration($authClient, $registration);
            $showAuthClients = false;
        } else {
            Yii::warning('Registration failed: No token (query) or authclient (session) found!', 'user');
            Yii::$app->session->setFlash('error', 'Registration failed.');
            return $this->redirect(['/user/auth/login']);
        }

        if ($registration->submitted('save') && $registration->validate() && $registration->register($authClient)) {
            Yii::$app->session->remove('authClient');

            // Autologin when user is enabled (no approval required)
            if ($registration->getUser()->status === User::STATUS_ENABLED) {
                Yii::$app->user->switchIdentity($registration->models['User']);
                $registration->models['User']->updateAttributes(['last_login' => date('Y-m-d G:i:s')]);
                if (Yii::$app->request->getIsAjax()) {
                    return $this->htmlRedirect(Yii::$app->user->returnUrl);
                }
                return $this->redirect(Yii::$app->user->returnUrl);
            }

            return $this->render('success', [
                'form' => $registration,
                'needApproval' => ($registration->getUser()->status === User::STATUS_NEED_APPROVAL)
            ]);
        }

        return $this->render('index', [
            'hForm' => $registration,
            'showAuthClient' => $showAuthClients,
        ]);
    }


    /**
     * Invitation by link
     * @param $token
     * @param $spaceId
     * @return string
     * @throws HttpException
     */
    public function actionByLink($token = null, $spaceId = null)
    {
        AuthHelper::handleInviteByLinkRegistration($token, $spaceId);

        // Check if all external auth clients can accept params in the return URL allowing to skip email validation
        $allAuthClientsCanSkipEmailValidation = true;
        foreach ((new AuthChoice())->clients as $client) {
            if (!property_exists($client, 'parametersToKeepInReturnUrl')) {
                $allAuthClientsCanSkipEmailValidation = false;
            }
        }

        $invite = new Invite([
            'source' => Invite::SOURCE_INVITE_BY_LINK,
            'space_invite_id' => $spaceId,
            'scenario' => 'invite',
            'language' => Yii::$app->language,
        ]);

        if ($invite->load(Yii::$app->request->post()) && $invite->save()) {
            $invite->sendInviteMail();
            return $this->render('@user/views/auth/register_success', ['model' => $invite]);
        }

        return $this->render('byLink', [
            'invite' => $invite,
            'showAuthClients' => $allAuthClientsCanSkipEmailValidation,
        ]);
    }

    /**
     * @param $inviteToken
     * @param Registration $form
     * @throws HttpException
     */
    protected function handleInviteRegistration($inviteToken, Registration $form)
    {
        $userInvite = Invite::findOne(['token' => $inviteToken]);
        if (!$userInvite) {
            throw new HttpException(404, 'Invalid registration token!');
        }
        Yii::$app->setLanguage($userInvite->language);
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
