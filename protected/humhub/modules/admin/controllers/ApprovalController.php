<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\components\access\ControllerAccess;
use humhub\modules\admin\models\UserApprovalSearch;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\HttpException;
use humhub\modules\user\models\User;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\forms\ApproveUserForm;

/**
 * ApprovalController handels new user approvals
 */
class ApprovalController extends Controller
{
    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->subLayout = '@admin/views/layouts/user';
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Approval'));

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY],
            ['checkCanApproveUsers'],
        ];
    }

    /**
     * @param $rule
     * @param $access
     * @return bool
     * @throws \Throwable
     */
    public function checkCanApproveUsers($rule, $access)
    {
        if (!Yii::$app->user->getIdentity()->canApproveUsers()) {
            $access->code = 403;
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!Yii::$app->user->isAdmin()) {
            $this->subLayout = "@humhub/modules/admin/views/approval/_layoutNoAdmin";
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $searchModel = new UserApprovalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }

    public function actionApprove($id)
    {
        $user = $this->getUser($id);

        $model = new ApproveUserForm;
        $model->subject = Yii::t('AdminModule.user', "Account Request for '{displayName}' has been approved.", ['{displayName}' => Html::encode($user->displayName)]);
        $model->message = strtr(Yii::$app->getModule('user')->settings->get('auth.registrationApprovalMailContent', Yii::t('AdminModule.user', \humhub\modules\admin\models\forms\AuthenticationSettingsForm::defaultRegistrationApprovalMailContent)), [
                    '{displayName}' => Html::encode($user->displayName),
                    '{loginURL}' => urldecode(Url::to(["/user/auth/login"], true)),
                    '{AdminName}' => Yii::$app->user->getIdentity()->displayName,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->send($user->email);
            $user->status = User::STATUS_ENABLED;
            $user->save();
            $user->setUpApproved();
            return $this->redirect(['index']);
        }

        return $this->render('approve', [
            'model' => $user,
            'approveFormModel' => $model
        ]);
    }

    public function actionDecline($id)
    {
        $user = $this->getUser($id);

        $model = new ApproveUserForm;
        $model->subject = Yii::t('AdminModule.user', 'Account Request for \'{displayName}\' has been declined.', ['{displayName}' => Html::encode($user->displayName)]);
        $model->message = strtr(Yii::$app->getModule('user')->settings->get('auth.registrationDenialMailContent', Yii::t('AdminModule.user', \humhub\modules\admin\models\forms\AuthenticationSettingsForm::defaultRegistrationDenialMailContent)), [
                    '{displayName}' => Html::encode($user->displayName),
                    '{AdminName}' => Yii::$app->user->getIdentity()->displayName,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->send($user->email);
            $user->delete();
            return $this->redirect(['index']);
        }

        return $this->render('decline', [
            'model' => $user,
            'approveFormModel' => $model
        ]);
    }

    private function getUser($id)
    {
        $user = User::find()
            ->andWhere(['user.id' => (int)Yii::$app->request->get('id'), 'user.status' => User::STATUS_NEED_APPROVAL])
            ->administrableBy(Yii::$app->user->getIdentity())
            ->one();

        if ($user == null) {
            throw new HttpException(404, Yii::t('AdminModule.controllers_ApprovalController', 'User not found!'));
        }

        return $user;
    }

}
