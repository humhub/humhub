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
use humhub\modules\user\authclient\BaseFormClient;
use humhub\modules\user\models\forms\Registration;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use humhub\modules\user\services\InviteRegistrationService;
use humhub\modules\user\services\LinkRegistrationService;
use humhub\modules\user\services\PendingAuthService;
use humhub\modules\user\services\UserSourceService;
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
        $pendingAuth = new PendingAuthService();

        if (Yii::$app->request->get('token')) {
            $inviteRegistrationService = new InviteRegistrationService(Yii::$app->request->get('token'));
            if (!$inviteRegistrationService->isValid()) {
                throw new HttpException(404, 'Invalid registration token!');
            }

            // Invite links shouldn't fall back to the local form when it's not
            // meant to be shown (see redirectToExternalAuthClient()).
            if (($response = $this->redirectToExternalAuthClient()) !== null) {
                return $response;
            }

            $inviteRegistrationService->populateRegistration($registration);
        } elseif ($pendingAuth->hasPending()) {
            $authClient = $pendingAuth->restore();
            $registration = $this->createRegistrationByAuthClient($authClient);
        } else {
            Yii::warning('Registration failed: No token (query) or pending auth (session) found!', 'user');
            Yii::$app->session->setFlash('error', 'Registration failed.');
            return $this->redirect(['/user/auth/login']);
        }

        $registration->setForm();

        if ($registration->submitted('save') && $registration->register($authClient)) {
            $pendingAuth->clear();

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
            'showRegistrationForm' => AuthController::isSelfRegistrationFormVisible(),
            'hasAuthClient' => $authClient !== null,
        ]);
    }


    /**
     * Renders the registration success view for users whose account was just
     * created via an auth client (SSO) and is awaiting admin approval.
     *
     * The auth flow ({@see AuthController::register()}) redirects here instead
     * of attempting to log the user in immediately, which would bounce them
     * back to the login form with a bare "not approved yet" flash error.
     *
     * @since 1.19
     */
    public function actionPending()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        return $this->render('success', [
            'needApproval' => true,
        ]);
    }

    /**
     * Invitation by link
     * @param null $token
     * @param null $spaceId
     * @return string
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

        // If local registration is disabled and exactly one external auth
        // client remains, send the user directly into that auth client's
        // flow instead of showing the "invite by link" landing page. The
        // space invite survives the round trip via LinkRegistrationService's
        // session state (see storeInSession() above).
        if (($response = $this->redirectToExternalAuthClient()) !== null) {
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
            'showRegistrationForm' => AuthController::isSelfRegistrationFormVisible(),
            'showAuthClients' => true,
        ]);
    }

    /**
     * When the local registration form isn't meant to be shown — see
     * {@see AuthController::isSelfRegistrationFormVisible()} — invite links
     * (by e-mail token or by shared link) shouldn't fall back to it either.
     * Instead, send the invitee straight into the sole applicable external
     * auth client's flow, the pre-login counterpart of
     * {@see \humhub\modules\user\models\forms\LoginIdentity::getStep1Redirect()}.
     *
     * There's no existing User yet to resolve a UserSource from, so instead
     * of {@see UserSourceService::getForUser()} this looks at
     * {@see \humhub\modules\user\source\LocalUserSource}, the source new
     * invitees are provisioned into.
     *
     * The invite itself survives the round trip without any extra state:
     * for e-mail invites, {@see InviteRegistrationService::createFromRequestOrEmail()}
     * re-resolves the token by matching the IdP-returned e-mail once the user
     * comes back through {@see AuthController::register()}; for link invites,
     * {@see LinkRegistrationService::storeInSession()} (already called by
     * actionByLink() before this runs) keeps the space association in session.
     *
     * Returns `null` when the local form should still be rendered — because
     * it's visible, or because no single unambiguous external client could
     * be determined.
     */
    private function redirectToExternalAuthClient(): ?Response
    {
        if (AuthController::isSelfRegistrationFormVisible()) {
            return null;
        }

        $authClientId = $this->findSoleExternalAuthClientId();

        return $authClientId === null
            ? null
            : $this->redirect(['/user/auth/external', 'authclient' => $authClientId]);
    }

    /**
     * Preference 1 (mirrors LoginIdentity::getStep1Redirect()): an explicit
     * allow-list configured on LocalUserSource names the IdP directly — e.g.
     * an SSO-only deployment that wired `LocalUserSource::$allowedAuthClientIds`.
     *
     * Preference 2 — the common case where no such allow-list was configured
     * (an empty list means "all clients allowed", see
     * {@see \humhub\modules\user\source\BaseUserSource::$allowedAuthClientIds}):
     * fall back to "exactly one non-form auth client is configured at all",
     * e.g. a bare SSO module install that only adds one AuthClient.
     *
     * Returns `null` when neither preference yields a single unambiguous
     * external client.
     */
    private function findSoleExternalAuthClientId(): ?string
    {
        $collection = Yii::$app->authClientCollection;
        $allowedAuthClientIds = UserSourceService::getCollection()->getLocalUserSource()->getAllowedAuthClientIds();

        if (!empty($allowedAuthClientIds)) {
            $firstId = reset($allowedAuthClientIds);
            if ($collection->hasClient($firstId) && !($collection->getClient($firstId) instanceof BaseFormClient)) {
                return $firstId;
            }
            return null;
        }

        $externalClients = array_filter(
            $collection->getClients(),
            static fn($client) => !$client instanceof BaseFormClient,
        );

        return count($externalClients) === 1 ? reset($externalClients)->getId() : null;
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

        $userSource = UserSourceService::getCollection()->findUserSourceForAuthClient($authClient->getId(), $attributes);
        $registration->enableUserApproval = $userSource->requiresApproval($authClient->getId());

        // do not store id attribute
        unset($attributes['id']);

        $registration->getUser()->setAttributes($attributes, false);
        $registration->getProfile()->setAttributes($attributes, false);

        // Pin user_source to the dispatching source. Otherwise User::beforeSave()
        // falls back to 'local' and a user that should have been provisioned via
        // LdapUserSource ends up as a local user — breaking later LDAP login/sync.
        $registration->getUser()->user_source = $userSource->getId();

        return $registration;
    }
}
