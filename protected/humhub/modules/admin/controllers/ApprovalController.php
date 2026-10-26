<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\access\DelegateAccessValidator;
use humhub\components\listing\ListContext;
use humhub\helpers\Html;
use humhub\modules\admin\components\ApprovalTable;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\forms\ApproveUserBulkMessageForm;
use humhub\modules\admin\models\forms\ApproveUserForm;
use humhub\modules\admin\widgets\ApprovalTable as ApprovalTableWidget;
use humhub\modules\user\models\User;
use humhub\widgets\modal\ModalClose;
use Yii;

/**
 * ApprovalController handles new user approvals
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
        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY],
            ['checkCanManageUsers'],
        ];
    }

    /**
     * Check the current user can manage other users
     *
     * @param array $rule
     * @param DelegateAccessValidator $access
     * @return bool
     */
    public function checkCanManageUsers($rule, $access): bool
    {
        if (Yii::$app->user->getIdentity()->canManageUsers()) {
            return true;
        }

        $access->code = 403;
        return false;
    }

    /**
     * The registrations awaiting approval (`admin\widgets\ApprovalTable`, its rows from
     * `GET /api/v2/admin/approval`)
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * "Send a message" for the selected rows of the approval table: the dialog (GET, the users in
     * `ids[]`, or `all` with the list's filters), and sending it (POST).
     */
    public function actionBulkMessage()
    {
        $request = Yii::$app->request;
        $model = new ApproveUserBulkMessageForm();

        if ($request->isPost) {
            $model->load($request->post());
            $users = $this->approvalUsers($model->ids, false, []);
            if ($model->validate()) {
                $sent = $model->send($users);
                // The table loads its rows (the messages sent) again
                $refresh = self::refreshTableScript();
                return $sent === count($users)
                    ? ModalClose::widget(['script' => $refresh, 'success' => Yii::t('AdminModule.user', '{count, plural, =1{1 user was notified by email} other{# users were notified by email}}.', ['count' => $sent])])
                    : ModalClose::widget(['script' => $refresh, 'error' => Yii::t('AdminModule.user', 'Could not send the message to {count} of {total} users.', ['count' => count($users) - $sent, 'total' => count($users)])]);
            }
        } else {
            $users = $this->approvalUsers((array)$request->get('ids', []), (bool)$request->get('all'), $request->get());
            $model->setDefaults();
        }

        $model->ids = array_map(static fn(User $user) => (int)$user->id, $users);

        return $this->renderAjax('bulk-message', ['model' => $model]);
    }

    /**
     * The registrations of the approval table a message may be sent to: the given ones, or all of
     * the list's filters.
     *
     * @return User[]
     */
    private function approvalUsers(array $ids, bool $all, array $params): array
    {
        $table = Yii::createObject(ApprovalTable::class);
        $params = array_intersect_key($params, array_flip(['q']));
        $query = $table->getList()->build($params, ListContext::forCurrentUser())->query();
        if (!$all) {
            $query->andWhere(['user.id' => array_map('intval', $ids)]);
        }

        $action = $table->getAction('message');

        return array_values(array_filter($query->all(), static fn(User $user) => $action === null || $action->isVisibleFor($user)));
    }

    /**
     * The dialog of a message to a registration awaiting approval.
     */
    public function actionSendMessage($id)
    {
        $model = new ApproveUserForm($id);
        $model->setSendMessageDefaults();

        return $this->handleApprovalForm($model, [
            'run' => static fn() => $model->sendMessage(),
            'title' => Html::encode(Yii::t('AdminModule.user', 'Send a message to {displayName}', ['displayName' => $model->user->displayName])),
            'submit' => Yii::t('AdminModule.user', 'Send'),
            'success' => Yii::t('AdminModule.user', 'The message has been sent by email.'),
            'failure' => Yii::t('AdminModule.user', 'Could not send the message to the user!'),
        ]);
    }

    /**
     * The dialog approving a registration, with the message to the user.
     */
    public function actionApprove($id)
    {
        $model = new ApproveUserForm($id);
        $model->setApprovalDefaults();
        $userId = (int)$model->user->id;

        return $this->handleApprovalForm($model, [
            // Approved even if the notification could not be sent
            'run' => static fn() => $model->approve()
                || (int)User::find()->select('status')->where(['id' => $userId])->scalar() === User::STATUS_ENABLED,
            'title' => Html::encode(Yii::t('AdminModule.user', 'Accept user: {displayName}', ['displayName' => $model->user->displayName])),
            'submit' => Yii::t('AdminModule.user', 'Send & save'),
            'success' => Yii::t('AdminModule.user', 'The registration was approved and the user was notified by email.'),
            'failure' => Yii::t('AdminModule.user', 'Could not approve the user!'),
        ]);
    }

    /**
     * The dialog declining a registration (the account is deleted), with the message to the user.
     */
    public function actionDecline($id)
    {
        $model = new ApproveUserForm($id);
        $model->setDeclineDefaults();
        $userId = (int)$model->user->id;

        return $this->handleApprovalForm($model, [
            // Declined even if the notification could not be sent
            'run' => static fn() => $model->decline() || !User::find()->where(['id' => $userId])->exists(),
            'title' => Html::encode(Yii::t('AdminModule.user', 'Decline & delete user: {displayName}', ['displayName' => $model->user->displayName])),
            'submit' => Yii::t('AdminModule.user', 'Send & decline'),
            'success' => Yii::t('AdminModule.user', 'The registration was declined and the user was notified by email.'),
            'failure' => Yii::t('AdminModule.user', 'Could not decline the user!'),
            'destructive' => true,
        ]);
    }

    /**
     * The dialog of a single registration of the approval table, and running it when posted: the
     * dialog closes and the table loads its rows again. Outside the table (no AJAX), the table.
     *
     * @param array $options `run` (`fn(): bool`), `title` (HTML), `submit`, `success`,
     * `failure`, `destructive`
     */
    private function handleApprovalForm(ApproveUserForm $model, array $options)
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post())) {
            $done = ($options['run'])();
            return ModalClose::widget([
                'script' => self::refreshTableScript(),
                $done ? 'success' : 'error' => $done ? $options['success'] : $options['failure'],
            ]);
        }

        return $this->renderAjax('_dialog', [
            'approveFormModel' => $model,
            'title' => $options['title'],
            'submit' => $options['submit'],
            'destructive' => $options['destructive'] ?? false,
        ]);
    }

    /**
     * The script asking the approval table to load its rows again.
     */
    private static function refreshTableScript(): string
    {
        return 'humhub.require("event").trigger("humhub:data-table:refresh", {tableId: ' . json_encode(ApprovalTableWidget::TABLE_ID) . '});';
    }
}
