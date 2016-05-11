<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use humhub\modules\user\models\User;
use humhub\modules\admin\components\Controller;
use humhub\components\behaviors\AccessControl;
use humhub\modules\admin\models\forms\ApproveUserForm;

/**
 * ApprovalController handels new user approvals
 */
class ApprovalController extends Controller
{

    public function init()
    {
        $this->subLayout = '@admin/views/layouts/user';
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Approval'));
        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::className(),
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!Yii::$app->user->getIdentity()->canApproveUsers()) {
            throw new ForbiddenHttpException(Yii::t('error', 'You are not allowed to perform this action.'));
        }

        if (!Yii::$app->user->isAdmin()) {
            $this->subLayout = "@humhub/modules/admin/views/approval/_layoutNoAdmin";
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $searchModel = new \humhub\modules\admin\models\UserApprovalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', array(
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel
        ));
    }

    public function actionApprove()
    {
        $user = User::findOne(['id' => (int) Yii::$app->request->get('id')]);

        if ($user == null)
            throw new HttpException(404, Yii::t('AdminModule.controllers_ApprovalController', 'User not found!'));

        $model = new ApproveUserForm;
        $model->subject = Yii::t('AdminModule.controllers_ApprovalController', "Account Request for '{displayName}' has been approved.", array('{displayName}' => Html::encode($user->displayName)));
        $model->message = Yii::t('AdminModule.controllers_ApprovalController', 'Hello {displayName},<br><br>

   your account has been activated.<br><br>

   Click here to login:<br>
   <a href=\'{loginURL}\'>{loginURL}</a><br><br>

   Kind Regards<br>
   {AdminName}<br><br>', array(
                    '{displayName}' => Html::encode($user->displayName),
                    '{loginURL}' => urldecode(Url::to(["/user/auth/login"], true)),
                    '{AdminName}' => Yii::$app->user->getIdentity()->displayName,
        ));

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->send($user->email);
            $user->status = User::STATUS_ENABLED;
            $user->save();
            $user->setUpApproved();
            return $this->redirect(['index']);
        }

        return $this->render('approve', ['model' => $user, 'approveFormModel' => $model]);
    }

    public function actionDecline()
    {

        $user = User::findOne(['id' => (int) Yii::$app->request->get('id')]);

        if ($user == null)
            throw new HttpException(404, Yii::t('AdminModule.controllers_ApprovalController', 'User not found!'));

        $model = new ApproveUserForm;
        $model->subject = Yii::t('AdminModule.controllers_ApprovalController', 'Account Request for \'{displayName}\' has been declined.', array('{displayName}' => Html::encode($user->displayName)));
        $model->message = Yii::t('AdminModule.controllers_ApprovalController', 'Hello {displayName},<br><br>

   your account request has been declined.<br><br>

   Kind Regards<br>
   {AdminName}<br><br>', array(
                    '{displayName}' => Html::encode($user->displayName),
                    '{AdminName}' => Yii::$app->user->getIdentity()->displayName,
        ));

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->send($user->email);
            $user->delete();
            return $this->redirect(['index']);
        }

        return $this->render('decline', ['model' => $user, 'approveFormModel' => $model]);
    }

}

?>
