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

    /**
     * @param $id
     * @return string
     * @throws HttpException
     * @throws \Throwable
     */
    public function actionApprove($id)
    {
        $model = new ApproveUserForm($id);
        $model->setApprovalDefaults();
        if($model->load(Yii::$app->request->post()) && $model->approve()) {
            return $this->redirect(['index']);
        }

        return $this->render('approve', [
            'model' => $model->user,
            'approveFormModel' => $model
        ]);
    }

    public function actionDecline($id)
    {
        $model = new ApproveUserForm($id);
        $model->setDeclineDefaults();
        if($model->load(Yii::$app->request->post()) && $model->decline()) {
            return $this->redirect(['index']);
        }

        return $this->render('decline', [
            'model' => $model->user,
            'approveFormModel' => $model
        ]);
    }
}
