<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\behaviors\AccessControl;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\forms\Invite as InviteForm;
use humhub\widgets\ModalClose;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\Controller;
use yii\web\HttpException;

/**
 * InviteController for new user invites
 *
 * @since 1.1
 */
class InviteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
            ],
        ];
    }

    /**
     * Invite form and processing action
     *
     * @return string the action result
     * @throws HttpException
     */
    public function actionIndex()
    {
        $model = new InviteForm();

        if ($target = Yii::$app->request->get('target')) {
            $model->target = $target;
        }

        $canInviteByEmail = $model->canInviteByEmail();
        $canInviteByLink = $model->canInviteByLink();
        if (!$canInviteByEmail && !$canInviteByLink) {
            throw new HttpException(403, 'Invite denied!');
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            foreach ($model->getEmails() as $email) {
                $this->createInvite($email);
            }

            return ModalClose::widget([
                'success' => Yii::t('UserModule.base', 'User has been invited.'),
            ]);
        }

        return $this->renderAjax('index', [
            'model' => $model,
            'canInviteByEmail' => $canInviteByEmail,
            'canInviteByLink' => $canInviteByLink,
        ]);
    }

    /**
     * Creates and sends an e-mail invite
     *
     * @param email $email
     */
    protected function createInvite($email)
    {
        $userInvite = new Invite();
        $userInvite->email = $email;
        $userInvite->source = Invite::SOURCE_INVITE;
        $userInvite->user_originator_id = Yii::$app->user->getIdentity()->id;
        $userInvite->language = Yii::$app->settings->get('defaultLanguage');

        $existingInvite = Invite::findOne(['email' => $email]);
        if ($existingInvite !== null) {
            $userInvite->token = $existingInvite->token;
            $existingInvite->delete();
        }

        $userInvite->save();
        $userInvite->sendInviteMail();
    }

    /**
     * @return string
     * @throws Throwable
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function actionResetInviteLink()
    {
        $model = new InviteForm();

        if ($target = Yii::$app->request->get('target')) {
            $model->target = $target;
        }

        if (!Yii::$app->user->can([ManageUsers::class, ManageGroups::class])) {
            $this->forbidden();
        }

        $model->getInviteLink(true);

        $this->view->saved();

        return $this->renderAjax('index', [
            'model' => $model,
            'canInviteByEmail' => $model->canInviteByEmail(),
            'canInviteByLink' => $model->canInviteByLink(),
        ]);
    }
}
