<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\modules\space\models\forms\InviteForm;
use humhub\modules\space\models\Space;
use humhub\modules\user\Module;
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
        $inviteByEmailToken = Yii::$app->request->get('token', '');
        $inviteByLinkToken = Yii::$app->request->get('inviteByLinkToken');
        $inviteSpaceId = Yii::$app->request->get('inviteSpaceId');

        if ($inviteByEmailToken != '') {
            $this->handleInviteByEmailRegistration($inviteByEmailToken, $registration);
        } elseif ($inviteByLinkToken) {
            $this->handleInviteByLinkRegistration($inviteByLinkToken, $inviteSpaceId, true);
        } elseif (Yii::$app->session->has('authClient')) {
            $authClient = Yii::$app->session->get('authClient');
            $this->handleAuthClientRegistration($authClient, $registration);
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

        return $this->render('index', ['hForm' => $registration]);
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
        /* @var $module Module */
        $module = Yii::$app->getModule('user');
        if (!$module->emailRequired) {
            // Bypass email form
            $this->redirect(['index', 'inviteByLinkToken' => $token, 'inviteSpaceId' => $spaceId]);
        }

        $this->handleInviteByLinkRegistration($token, $spaceId);

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
        ]);
    }

    /**
     * @param $inviteToken
     * @param Registration $form
     * @throws HttpException
     */
    protected function handleInviteByEmailRegistration($inviteToken, Registration $form)
    {
        $userInvite = Invite::findOne(['token' => $inviteToken]);
        if (!$userInvite) {
            throw new HttpException(404, 'Invalid registration token!');
        }
        Yii::$app->setLanguage($userInvite->language);
        $form->getUser()->email = $userInvite->email;
    }

    /**
     * @param $inviteToken
     * @param $spaceId
     * @param bool $setSpaceIdInSession If email is not required, we need to keep the invite space ID in session to add the user to the space in User::setUpApproved()
     * @throws HttpException
     */
    protected function handleInviteByLinkRegistration($inviteToken, $spaceId, $setSpaceIdInSession = false)
    {
        if (empty(Yii::$app->getModule('user')->settings->get('auth.internalUsersCanInviteByLink'))) {
            throw new HttpException(400, 'Invite by link is disabled!');
        }

        // If invited by link from a space
        if ($spaceId !== null) {
            $space = Space::findOne($spaceId);
            if (
                $space !== null
                && $space->settings->get('inviteToken') === $inviteToken
            ) {
                Yii::$app->setLanguage($space->ownerUser->language);
                if ($setSpaceIdInSession) {
                    Yii::$app->session->set(InviteForm::SESSION_SPACE_INVITE_ID, $spaceId);
                }
                return;
            }
            throw new HttpException(404, 'Invalid registration token!');
        }

        // If invited by link globally
        /* @var $module Module */
        $module = Yii::$app->getModule('user');
        if ($module->settings->get('registration.inviteToken') !== $inviteToken) {
            throw new HttpException(404, 'Invalid registration token!');
        }
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
