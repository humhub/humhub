<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\components\access\ControllerAccess;
use humhub\modules\admin\models\UserApprovalSearch;
use humhub\modules\user\models\ProfileField;
use Yii;
use yii\web\HttpException;
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

    public const ACTION_APPROVE = 'approve';
    public const ACTION_DELINE = 'decline';

    public const USER_SETTINGS_SCREEN_KEY = 'admin_approval_screen_profile_fields_id';

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

    /**
     * @return string
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $searchModel = new UserApprovalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Get available profile fields for screen options
        $availableProfileFields = ProfileField::find()
            ->joinWith('category')
            ->orderBy([
                'profile_field_category.sort_order' => SORT_ASC,
                'profile_field.sort_order'=>SORT_ASC
            ])
            ->where(['profile_field.show_at_registration' => true])
            ->andWhere(['not', ['profile_field.internal_name' => ['firstname', 'lastname']]])
            ->all();

        // Get or set screen options
        $userSettings = Yii::$app->settings->user(Yii::$app->user->identity);
        $screenProfileFieldsId = $userSettings->getSerialized(self::USER_SETTINGS_SCREEN_KEY, []);
        if (Yii::$app->request->post('screenProfileFieldsId')) {
            $screenProfileFieldsId = Yii::$app->request->post('screenProfileFieldsId');
            $userSettings->setSerialized(self::USER_SETTINGS_SCREEN_KEY, $screenProfileFieldsId);
        }
        $profileFieldsColumns = !$screenProfileFieldsId ? [] : ProfileField::find()
            ->where(['id' => $screenProfileFieldsId])
            ->indexBy('id')
            ->all();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'availableProfileFields' => $availableProfileFields,
            'profileFieldsColumns' => $profileFieldsColumns,
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\console\Response|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionApprove($id)
    {
        $model = new ApproveUserForm($id);
        $model->setApprovalDefaults();
        if($model->load(Yii::$app->request->post()) && $model->approve()) {
            $this->view->success(Yii::t('AdminModule.controllers_ApprovalController', 'The user has been approved and the email has been sent'));
            return $this->redirect(['index']);
        }

        return $this->render('approve', [
            'model' => $model->user,
            'approveFormModel' => $model
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\console\Response|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDecline($id)
    {
        $model = new ApproveUserForm($id);
        $model->setDeclineDefaults();
        if($model->load(Yii::$app->request->post()) && $model->decline()) {
            $this->view->success(Yii::t('AdminModule.controllers_ApprovalController', 'The user has been declined and the email has been sent'));
            return $this->redirect(['index']);
        }

        return $this->render('decline', [
            'model' => $model->user,
            'approveFormModel' => $model
        ]);
    }

    /**
     * @return string|\yii\console\Response|\yii\web\Response
     * @throws HttpException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionBulkActions()
    {
        /** @var string $action */
        $action = Yii::$app->request->post('action');
        /** @var array $usersId */
        $usersId = Yii::$app->request->post('ids');

        if (!$action) {
            $this->view->error(Yii::t('AdminModule.controllers_ApprovalController', 'Please select an action (approve or decline)'));
            return $this->redirect(['index']);
        }

        if (!$usersId) {
            $this->view->error(Yii::t('AdminModule.controllers_ApprovalController', 'Please select some users (tick the checkboxes)'));
            return $this->redirect(['index']);
        }

        $model = new ApproveUserForm($usersId);

        if ($action === self::ACTION_APPROVE) {
            return $this->bulkApprove($model, $usersId);
        }
        if ($action === self::ACTION_DELINE) {
            return $this->bulkDecline($model, $usersId);
        }
        throw new HttpException(400);
    }

    /**
     * @param ApproveUserForm $model
     * @return string|\yii\console\Response|\yii\web\Response
     */
    protected function bulkApprove(ApproveUserForm $model)
    {
        if($model->load(Yii::$app->request->post()) && $model->bulkApprove()) {
            $this->view->success(Yii::t('AdminModule.controllers_ApprovalController', 'Users have been approved and emails have been sent'));
            return $this->redirect(['index']);
        }

        return $this->render('bulkApprove', [
            'users' => $model->users,
            'approveFormModel' => $model
        ]);
    }

    /**
     * @param ApproveUserForm $model
     * @return string|\yii\console\Response|\yii\web\Response
     * @throws \Throwable
     */
    protected function bulkDecline(ApproveUserForm $model)
    {
        if($model->load(Yii::$app->request->post()) && $model->bulkDecline()) {
            $this->view->success(Yii::t('AdminModule.controllers_ApprovalController', 'Users have been declined and emails have been sent'));
            return $this->redirect(['index']);
        }

        return $this->render('bulkDecline', [
            'users' => $model->users,
            'approveFormModel' => $model
        ]);
    }
}
