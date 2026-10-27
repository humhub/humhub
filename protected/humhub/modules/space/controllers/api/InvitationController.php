<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers\api;

use humhub\modules\space\models\Membership;
use humhub\modules\space\modules\manage\components\SpaceInvitationTable;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * The open invitations of a space as rows of its invitation table (`SpaceInvitationTable`):
 * `GET space/<id>/invitation` answers one page of them; `POST space/<id>/invitation/<userId>/cancel`
 * cancels an invitation (`{removed: true}`); `POST space/<id>/invitation/bulk/cancel` cancels the
 * selected ones (`ids`, or `all` of the list's filters in the query).
 *
 * For the space's administrators ({@see SpaceInvitationTable::canAccess()}).
 *
 * @since 1.20
 */
class InvitationController extends MembershipTableController
{
    /**
     * @inheritdoc
     */
    protected function tableClass(): string
    {
        return SpaceInvitationTable::class;
    }

    /**
     * @inheritdoc
     */
    protected function membershipStatus(): int
    {
        return Membership::STATUS_INVITED;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'actions' => [
                    'cancel' => ['POST'],
                    'bulk-cancel' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionCancel($userId)
    {
        $membership = $this->findMembership((int)$userId, 'cancel');
        $this->space()->removeMember($membership->user_id);

        return ['removed' => true];
    }

    /**
     * Cancels the selected invitations (user ids).
     */
    public function actionBulkCancel()
    {
        $space = $this->space();

        return $this->bulkAction(
            'cancel',
            $this->findMemberships(...),
            static fn(Membership $membership) => (bool)$space->removeMember($membership->user_id),
            static fn(int $done, int $skipped) => Yii::t('SpaceModule.manage', '{count, plural, =1{1 invitation was cancelled} other{# invitations were cancelled}}.', ['count' => $done]),
        );
    }
}
