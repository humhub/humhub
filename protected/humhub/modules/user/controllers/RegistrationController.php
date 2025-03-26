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

        if (Yii::$app->request->get('token')) {
            $inviteRegistrationService = new InviteRegistrationService(Yii::$app->request->get('token'));
            if (!$inviteRegistrationService->isValid()) {
                throw new HttpException(404, 'Invalid registration token!');
            }
            $inviteRegistrationService->populateRegistration($registration);
        } elseif (Yii::$app->session->has('authClient')) {
            $authClient = Yii::$app->session->get('authClient');
            $registration = $this->createRegistrationByAuthClient($authClient);
        } else {
            Yii::warning('Registration failed: No token (query) or authclient (session) found!', 'user');
            Yii::$app->session->setFlash('error', 'Registration failed.');
            return $this->redirect(['/user/auth/login']);
        }

        if ($registration->submitted('save') && $registration->validate() && $registration->register($authClient)) {
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
            throw new ForbiddenHttpException('Registration is disabled!');
        }

        if ($token === null || !$linkRegistrationService->isValid()) {
            throw new BadRequestHttpException('Invalid token provided!');
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
