<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\components\Response;
use humhub\modules\space\models\Space;
use humhub\modules\user\authclient\BaseFormAuth;
use humhub\modules\user\authclient\interfaces\ApprovalBypass;
use humhub\modules\user\models\forms\Registration;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use humhub\modules\user\services\InviteRegistrationService;
use humhub\modules\user\services\LinkRegistrationService;
use Throwable;
use Yii;
use yii\authclient\BaseClient;
use yii\authclient\ClientInterface;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

/**
 * RegistrationController handles new user registration
 *
 * @property Module $module
 * @since 1.1
 */
class RegistrationController extends Controller
{
    /**
     * Session key used to carry an invite token across an enforced external
     * auth client login round trip triggered from an invite link.
     *
     * @see redirectToForcedAuthClient()
     */
    private const SESSION_INVITE_TOKEN = self::class . '::inviteToken';

    /**
     * @inheritdoc
     */
    public $layout = '@humhub/modules/user/views/layouts/main';

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
            $linkRegistrationService = LinkRegistrationService::createFromRequest();

            if (
                $linkRegistrationService->isValid()
                && $linkRegistrationService->inviteToSpace(Yii::$app->user->identity)
            ) {
                return $this->redirect($linkRegistrationService->getSpace()->getUrl());
            }
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
         * @var BaseClient
         */
        $authClient = null;

        $token = Yii::$app->request->get('token');
        if ($token) {
            $inviteRegistrationService = new InviteRegistrationService($token);
            if (!$inviteRegistrationService->isValid()) {
                throw new HttpException(404, 'Invalid registration token!');
            }

            // If local login/registration is disabled and exactly one external auth
            // client remains, send invited users directly into that
            // auth client's flow instead of showing the local password form.
            if (($response = $this->redirectToForcedAuthClient($token)) !== null) {
                return $response;
            }

            $inviteRegistrationService->populateRegistration($registration);
        } elseif (Yii::$app->session->has('authClient')) {
            $authClient = Yii::$app->session->get('authClient');
            $registration = $this->createRegistrationByAuthClient($authClient);

            // Restore invite data (e.g. e-mail/language) after the user was redirected
            // through an enforced external auth client login triggered by an invite link.
            if ($pendingInviteToken = Yii::$app->session->get(self::SESSION_INVITE_TOKEN)) {
                Yii::$app->session->remove(self::SESSION_INVITE_TOKEN);
                $pendingInviteRegistrationService = new InviteRegistrationService($pendingInviteToken);
                if ($pendingInviteRegistrationService->isValid()) {
                    $pendingInviteRegistrationService->populateRegistration($registration);
                }
            }
        } else {
            Yii::warning('Registration failed: No token (query) or authclient (session) found!', 'user');
            Yii::$app->session->setFlash('error', 'Registration failed.');
            return $this->redirect(['/user/auth/login']);
        }

        $registration->setForm();

        if ($registration->submitted('save') && $registration->register($authClient)) {
            Yii::$app->session->remove('authClient');

            // Autologin when user is enabled (no approval required)
            if ($registration->getUser()->status === User::STATUS_ENABLED) {
                $registration->getUser()->refresh(); // https://github.com/humhub/humhub/issues/6273
                Yii::$app->user->login($registration->getUser());
                if (Yii::$app->request->getIsAjax()) {
                    return $this->htmlRedirect(Yii::$app->user->returnUrl);
                }
                return $this->redirect(Yii::$app->user->returnUrl);
            }

            return $this->render('success', [
                'form' => $registration,
                'needApproval' => ($registration->getUser()->status === User::STATUS_NEED_APPROVAL),
            ]);
        }

        return $this->render('index', [
            'hForm' => $registration,
            'hasAuthClient' => $authClient !== null,
        ]);
    }


    /**
     * Invitation by link
     *
     * @param string|null $token
     * @param int|null $spaceId
     * @throws HttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionByLink(?string $token = null, $spaceId = null)
    {
        $linkRegistrationService = new LinkRegistrationService($token, Space::findOne(['id' => (int)$spaceId]));

        if (!$linkRegistrationService->isEnabled()) {
            throw new ForbiddenHttpException('Registration is disabled!');
        }

        if ($token === null || !$linkRegistrationService->isValid()) {
            throw new BadRequestHttpException('Invalid token provided!');
        }

        $linkRegistrationService->storeInSession();

        // If local login/registration is disabled and exactly one external auth
        // client (e.g. SAML) remains, send the user directly into that auth client's
        // flow instead of showing the "invite by link" landing page. The space
        // invite itself survives the round trip via LinkRegistrationService's
        // session state (see storeInSession() above).
        if (($response = $this->redirectToForcedAuthClient()) !== null) {
            return $response;
        }

        $form = new Invite([
            'source' => Invite::SOURCE_INVITE_BY_LINK,
            'scenario' => Invite::SCENARIO_INVITE_BY_LINK_FORM,
        ]);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            if ($invite = $linkRegistrationService->convertToInvite($form->email)) {
                $invite->sendInviteMail();
            } else {
                $invite = new Invite(['email' => $form->email]);
            }
            return $this->render('@user/views/auth/register_success', ['model' => $invite]);
        }

        return $this->render('byLink', [
            'invite' => $form,
            'showRegistrationForm' => $this->module->showRegistrationForm,
            'showAuthClients' => true,
        ]);
    }

    /**
     * When the local registration/login form is disabled (e.g. "Force SAML" setups
     * with only an SSO provider enabled), invite links (by e-mail token or by shared
     * link) should not fall back to the local password registration form. Instead,
     * redirect straight into the sole remaining external auth client's flow,
     * mirroring what happens for direct logins.
     *
     * Returns `null` if the local form should still be rendered (e.g. because it is
     * not disabled, the request has `?showRegistrationForm=1`, or there isn't exactly
     * one unambiguous external auth client).
     *
     * @param string|null $inviteToken the e-mail invite token (from actionIndex()), kept
     *  in session so it can be restored once the auth client flow redirects back to this
     *  controller. Not needed for the "invite by link" flow (actionByLink()), since that
     *  already persists its own state via LinkRegistrationService::storeInSession().
     */
    private function redirectToForcedAuthClient(?string $inviteToken = null): ?Response
    {
        if ($this->module->showRegistrationForm) {
            return null;
        }

        $externalClients = array_filter(
            Yii::$app->get('authClientCollection')->getClients(),
            static fn ($client) => !$client instanceof BaseFormAuth,
        );

        if (count($externalClients) !== 1) {
            return null;
        }

        /** @var BaseClient $externalClient */
        $externalClient = reset($externalClients);

        if ($inviteToken !== null) {
            Yii::$app->session->set(self::SESSION_INVITE_TOKEN, $inviteToken);
        }

        return $this->redirect(['/user/auth/external', 'authclient' => $externalClient->getId()]);
    }

    /**
     * Already all registration data gathered
     *
     * @param BaseClient $authClient
     * @param Registration $registration
     * @throws Exception
     */
    protected function createRegistrationByAuthClient(ClientInterface $authClient): Registration
    {
        $attributes = $authClient->getUserAttributes();

        if (!isset($attributes['id'])) {
            throw new Exception("No user id given by authclient!");
        }

        $registration = new Registration(enablePasswordForm: false);

        if ($authClient instanceof ApprovalBypass) {
            $registration->enableUserApproval = false;
        }

        // do not store id attribute
        unset($attributes['id']);

        $registration->getUser()->setAttributes($attributes, false);
        $registration->getProfile()->setAttributes($attributes, false);

        return $registration;
    }
}
