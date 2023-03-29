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
use yii\db\StaleObjectException;
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

        if ($inviteToken != '') {
            $this->handleInviteRegistration($inviteToken, $registration);
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
     * @param null $token
     * @param null $spaceId
     * @return string
     * @throws HttpException
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function actionByLink($token = null, $spaceId = null)
    {
        if (empty($this->module->settings->get('auth.internalUsersCanInviteByLink'))) {
            throw new HttpException(400, 'Invite by link is disabled!');
        }

        if ($spaceId !== null) {
            // If invited by link from a space
            $space = Space::findOne(['id' => (int)$spaceId]);
            if ($space === null || $space->settings->get('inviteToken') !== $token) {
                throw new HttpException(404, 'Invalid registration token!');
            }

            Yii::$app->setLanguage($space->ownerUser->language);
        } else {
            // If invited by link globally
            if ($this->module->settings->get('registration.inviteToken') !== $token) {
                throw new HttpException(404, 'Invalid registration token!');
            }
        }

        $invite = new Invite([
            'source' => Invite::SOURCE_INVITE_BY_LINK,
            'space_invite_id' => $spaceId,
            'scenario' => 'invite',
            'language' => Yii::$app->language,
        ]);

        if ($invite->load(Yii::$app->request->post())) {
            // Deleting any previous email invitation or abandoned link invitation
            $oldInvite = Invite::findOne(['email' => $invite->email]);
            if ($oldInvite !== null) {
                $oldInvite->delete();
            }
            if ($invite->save()) {
                $invite->sendInviteMail();
                return $this->render('@user/views/auth/register_success', ['model' => $invite]);
            }
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
