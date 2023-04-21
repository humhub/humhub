<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\modules\space\models\Space;
use humhub\modules\user\authclient\BaseFormAuth;
use humhub\modules\user\Module;
use humhub\modules\user\services\LinkRegistrationService;
use humhub\modules\user\services\InviteRegistrationService;
use humhub\modules\user\widgets\AuthChoice;
use Yii;
use yii\authclient\BaseClient;
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
         * @var BaseClient
         */
        $authClient = null;
        $showAuthClients = AuthChoice::hasClients();

        if (Yii::$app->request->get('token')) {
            $inviteRegistrationService = new InviteRegistrationService(Yii::$app->request->get('token'));
            if (!$inviteRegistrationService->isValid()) {
                throw new HttpException(404, 'Invalid registration token!');
            }
            $inviteRegistrationService->populateRegistration($registration);
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
            'showAuthClients' => $showAuthClients,
        ]);
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
        $linkRegistrationService = new LinkRegistrationService(Space::findOne(['id' => (int)$spaceId]));

        if (!$linkRegistrationService->isEnabled()) {
            throw new HttpException(404);
        }

        if ($token === null || !$linkRegistrationService->isValid($token)) {
            throw new HttpException(400, 'Invalid token provided!');
        }

        // Check if all external auth clients can accept params in the return URL allowing to skip email validation
        $allAuthClientsCanSkipEmailValidation = true;
        $collection = Yii::$app->get('authClientCollection');
        foreach ($collection->getClients() as $client) {
            if (
                !$client instanceof BaseFormAuth
                && !property_exists($client, 'parametersToKeepInReturnUrl')
            ) {
                $allAuthClientsCanSkipEmailValidation = false;
            }
        }

        $form = new Invite(['source' => Invite::SOURCE_INVITE_BY_LINK]);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $invite = $linkRegistrationService->convertToInvite($form->email);
            $invite->sendInviteMail();
            return $this->render('@user/views/auth/register_success', ['model' => $invite]);
        }

        return $this->render('byLink', [
            'invite' => $form,
            'showAuthClients' => $allAuthClientsCanSkipEmailValidation,
        ]);
    }

    /**
     * Already all registration data gathered
     *
     * @param BaseClient $authClient
     * @param Registration $registration
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
