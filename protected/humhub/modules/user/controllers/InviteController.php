<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageUsers;
use Yii;
use yii\web\Controller;
use humhub\modules\user\models\Invite;

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
                'class' => \humhub\components\behaviors\AccessControl::className(),
            ]
        ];
    }

    /**
     * Invite form and processing action
     * 
     * @return string the action result
     * @throws \yii\web\HttpException
     */
    public function actionIndex()
    {
        if (!$this->canInvite()) {
            throw new \yii\web\HttpException(403, 'Invite denied!');
        }

        $model = new \humhub\modules\user\models\forms\Invite;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            foreach ($model->getEmails() as $email) {
                $this->createInvite($email);
            }
            
            return \humhub\widgets\ModalClose::widget([
                'success' => Yii::t('UserModule.user', 'User has been invited.')
            ]);
        }

        return $this->renderAjax('index', array('model' => $model));
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
        
        $existingInvite = Invite::findOne(['email' => $email]);
        if ($existingInvite !== null) {
            $userInvite->token = $existingInvite->token;
            $existingInvite->delete();
        }
        
        $userInvite->save();
        $userInvite->sendInviteMail();
    }

    /**
     * Checks if current user can invite new members
     * 
     * @return boolean can invite new members
     */
    protected function canInvite()
    {
        return Yii::$app->getModule('user')->settings->get('auth.internalUsersCanInvite') || Yii::$app->user->can([new ManageUsers(), new ManageGroups()]);
    }

}

?>
