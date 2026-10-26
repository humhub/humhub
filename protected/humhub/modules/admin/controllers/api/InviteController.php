<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers\api;

use humhub\components\api\DataTableController;
use humhub\components\table\DataTable;
use humhub\modules\admin\components\PendingRegistrationTable;
use humhub\modules\user\models\Invite;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * The pending registrations as rows of their table (`PendingRegistrationTable`):
 * `GET admin/invite` answers one page of them; `POST admin/invite/<id>/resend` sends the
 * invitation again and answers the row; `POST admin/invite/<id>/delete` deletes it
 * (`{removed: true}`); `POST admin/invite/bulk/resend|delete` does so for the selected ones
 * (`ids`, or `all` of the list's filters in the query).
 *
 * For those who may manage users or groups ({@see PendingRegistrationTable::canAccess()}).
 *
 * @since 1.20
 */
class InviteController extends DataTableController
{
    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(PendingRegistrationTable::class);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'actions' => [
                    'resend' => ['POST'],
                    'delete' => ['POST'],
                    'bulk' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionResend($id)
    {
        $invite = $this->findInvite((int)$id);
        if (!$invite->sendInviteMail()) {
            return $this->validationErrors(['id' => [Yii::t('AdminModule.user', 'Cannot resend invitation email!')]]);
        }

        return ['row' => $this->getTable()->row($invite), 'message' => Yii::t('AdminModule.user', 'Resend invitation email')];
    }

    public function actionDelete($id)
    {
        $this->findInvite((int)$id)->delete();

        return ['removed' => true];
    }

    /**
     * Resends or deletes the selected invitations.
     */
    public function actionBulk($action)
    {
        if (!in_array($action, ['resend', 'delete'], true)) {
            throw new NotFoundHttpException();
        }

        return $this->bulkAction(
            $action,
            static fn(array $ids) => Invite::find()->where(['id' => $ids])->andWhere(Invite::filterSource()),
            static fn(Invite $invite) => $action === 'resend' ? $invite->sendInviteMail() : (bool)$invite->delete(),
            static fn(int $done) => $action === 'resend'
                ? Yii::t('AdminModule.user', '{count, plural, =1{1 invitation was sent again} other{# invitations were sent again}}.', ['count' => $done])
                : Yii::t('AdminModule.user', '{count, plural, =1{1 invitation was deleted} other{# invitations were deleted}}.', ['count' => $done]),
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

    private function findInvite(int $id): Invite
    {
        $invite = Invite::find()->where(['id' => $id])->andWhere(Invite::filterSource())->one();
        if ($invite === null) {
            throw new NotFoundHttpException();
        }

        return $invite;
    }
}
