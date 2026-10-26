<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers\api;

use humhub\components\api\DataTableController;
use humhub\components\listing\ListContext;
use humhub\components\table\DataTable;
use humhub\modules\admin\components\ApprovalTable;
use humhub\modules\admin\models\forms\ApproveUserForm;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * The registrations awaiting approval as rows of their table (`ApprovalTable`):
 * `GET admin/approval` answers one page of them; `POST admin/approval/bulk/approve|decline`
 * approves or declines the selected ones (`ids`, or `all` of the list's filters in the
 * query) with the default texts, each user notified by e-mail. A single registration is approved,
 * declined or messaged on its page, with a text of one's own; several are messaged through a
 * dialog of the web interface (`admin/approval/bulk-message`).
 *
 * For those who may manage users ({@see ApprovalTable::canAccess()}).
 *
 * @since 1.20
 */
class ApprovalController extends DataTableController
{
    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(ApprovalTable::class);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'actions' => [
                    'bulk' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * Approves or declines the selected registrations.
     */
    public function actionBulk($action)
    {
        if (!in_array($action, ['approve', 'decline'], true)) {
            throw new NotFoundHttpException();
        }

        $table = $this->getTable();

        return $this->bulkAction(
            $action,
            static fn(array $ids) => $table->getList()->build([], ListContext::forCurrentUser())->query()->andWhere(['user.id' => $ids]),
            // Counted by what happened to the account, as a failed notification mail does not
            // undo an approval
            static function (User $user) use ($action): bool {
                $form = new ApproveUserForm($user->id);
                return match ($action) {
                    'approve' => $form->approve() || (int)User::find()->select('status')->where(['id' => $user->id])->scalar() === User::STATUS_ENABLED,
                    'decline' => $form->decline() || !User::find()->where(['id' => $user->id])->exists(),
                };
            },
            static fn(int $done) => match ($action) {
                'approve' => Yii::t('AdminModule.user', '{count, plural, =1{1 registration was approved} other{# registrations were approved}}.', ['count' => $done]),
                'decline' => Yii::t('AdminModule.user', '{count, plural, =1{1 registration was declined} other{# registrations were declined}}.', ['count' => $done]),
            },
        );
    }

    /**
     * @inheritdoc
     * Without the bulk action of the route, which Yii puts among the query parameters.
     */
    protected function listParams(): array
    {
        return array_diff_key(parent::listParams(), ['action' => true]);
    }
}
