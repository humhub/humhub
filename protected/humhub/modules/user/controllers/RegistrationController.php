<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\space\models\Space;
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

        if (Yii::$app->request->get('token')) {
            $inviteRegistrationService = new InviteRegistrationService(Yii::$app->request->get('token'));
            if (!$inviteRegistrationService->isValid()) {
                throw new HttpException(404, 'Invalid registration token!');
            }
            $inviteRegistrationService->populateRegistration($registration);
        } elseif (Yii::$app->session->has('authClient')) {
            $authClient = Yii::$app->session->get('authClient');
            $this->handleAuthClientRegistration($authClient, $registration);
        }

        if ($registration->submitted('save') && $registration->validate()) {
            $existingUser = User::findOne(['email' => $registration->getUser()->email]);

            if ($existingUser) {
                // Log in the existing user
                Yii::$app->user->login($existingUser);

                if (Yii::$app->request->getIsAjax()) {
                    return $this->htmlRedirect(Yii::$app->user->returnUrl);
                }
                return $this->redirect(Yii::$app->user->returnUrl);
            }

            // Proceed with the registration process
            if ($registration->register($authClient)) {
                Yii::$app->session->remove('authClient');

                // Autologin when user is enabled (no approval required)
                if ($registration->getUser()->status === User::STATUS_ENABLED) {
                    // Log in the user
                    Yii::$app->user->login($registration->getUser());

                    if (Yii::$app->request->getIsAjax()) {
                        return $this->htmlRedirect(Yii::$app->user->returnUrl);
                    }
                    return $this->redirect(Yii::$app->user->returnUrl);
                }

                // If user requires approval, render success page
                return $this->render('success', [
                    'form' => $registration,
                    'needApproval' => ($registration->getUser()->status === User::STATUS_NEED_APPROVAL),
                ]);
            }
        }

        return $this->render('index', [
            'hForm' => $registration,
            'showRegistrationForm' => $this->module->showRegistrationForm,
            'hasAuthClient' => $authClient !== null,
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
            throw new HttpException(404);
        }

        if ($token === null || !$linkRegistrationService->isValid()) {
            throw new HttpException(400, 'Invalid token provided!');
        }

        $linkRegistrationService->storeInSession();

        $form = new Invite([
            'source' => Invite::SOURCE_INVITE_BY_LINK,
            'scenario' => Invite::SCENARIO_INVITE_BY_LINK_FORM,
        ]);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $invite = $linkRegistrationService->convertToInvite($form->email);
            $invite?->sendInviteMail();
            return $this->render('@user/views/auth/register_success', ['model' => $invite]);
        }

        return $this->render('byLink', [
            'invite' => $form,
            'showRegistrationForm' => $this->module->showRegistrationForm,
            'showAuthClients' => true,
        ]);
    }

    /**
     * Handles registration using attributes from the authentication client.
     *
     * @param ClientInterface $authClient The authentication client.
     * @param Registration $registration The registration data.
     * @throws \InvalidArgumentException If no user ID is provided by the auth client.
     * @throws \Exception If token or auth client is not found.
     */
    protected function handleAuthClientRegistration(ClientInterface $authClient, Registration $registration)
    {
        // Check if the necessary token or auth client is present
        if (!$this->isTokenAndAuthClientAvailable()) {
            throw new \Exception("Registration failed: No token (query) or authclient (session) found!");
        }

        $attributes = $authClient->getUserAttributes();

        if (!isset($attributes['id'])) {
            throw new \InvalidArgumentException("No user ID provided by auth client.");
        }

        $registration->enablePasswordForm = false;
        if ($authClient instanceof ApprovalBypass) {
            $registration->enableUserApproval = false;
        }

        // Do not store the 'id' attribute in the user or profile model.
        unset($attributes['id']);

        // Attempt to find the user by email
        $user = User::findOne(['email' => $attributes['email']]);

        if ($user) {
            // Existing user found, log in the user
            Yii::$app->user->login($user);
        } else {
            // User not found, proceed with registration
            $registration->getUser()->setAttributes($attributes, false);
            $registration->getProfile()->setAttributes($attributes, false);
        }
    }

    /**
     * Checks if the necessary token or auth client is available.
     *
     * @return bool True if token or auth client is available, false otherwise.
     */
    private function isTokenAndAuthClientAvailable(): bool
    {
        // Check if the necessary token is present in the query parameters
        $token = isset($_GET['token']) ? $_GET['token'] : null;

        // Check if the necessary auth client is present in the session variables
        $authClient = isset($_SESSION['authClient']) ? $_SESSION['authClient'] : null;

        // Return true if both token and auth client are available, false otherwise
        return !empty($token) && !empty($authClient);
    }
}
