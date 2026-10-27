<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers\api;

use humhub\modules\space\models\Membership;
use humhub\modules\space\modules\manage\components\SpaceMemberTable;
use humhub\modules\space\notifications\SpaceRolesChangedNotification;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * The members of a space as rows of its member table (`SpaceMemberTable`):
 * `GET space/<id>/member` answers one page of them (the list's filters and sorts as query
 * parameters) in the paginated envelope; `POST space/<id>/member/<userId>/role/<role>` gives a
 * member another role (`admin`, `moderator`, `member`; the member is notified) and answers the
 * new row; `POST space/<id>/member/<userId>/remove` removes a member (`{removed: true}`);
 * `POST space/<id>/member/bulk/remove` removes the selected members (`ids`, or `all` the members
 * of the list's filters in the query).
 *
 * For the space's administrators ({@see SpaceMemberTable::canAccess()}); each change only where
 * the member's row offers it (never on the owner).
 *
 * @since 1.20
 */
class MemberController extends MembershipTableController
{
    /**
     * @inheritdoc
     */
    protected function tableClass(): string
    {
        return SpaceMemberTable::class;
    }

    /**
     * @inheritdoc
     */
    protected function membershipStatus(): int
    {
        return Membership::STATUS_MEMBER;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'actions' => [
                    'role' => ['POST'],
                    'remove' => ['POST'],
                    'bulk-remove' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionRole($userId, $role)
    {
        $membership = $this->findMembership((int)$userId, 'role-' . $role);
        $membership->scenario = Membership::SCENARIO_EDIT_ROLE;
        $membership->group_id = (string)$role;
        if (!$membership->save()) {
            return $this->validationErrors($membership);
        }

        SpaceRolesChangedNotification::send(
            $membership->user,
            $membership,
            Yii::$app->user->identity,
            dedupe: false,
            payload: ['groupId' => $membership->group_id],
        );

        return ['row' => $this->getTable()->row($membership)];
    }

    public function actionRemove($userId)
    {
        $membership = $this->findMembership((int)$userId, 'remove');

        if (!$this->space()->removeMember($membership->user_id)) {
            return $this->validationErrors(['userId' => [Yii::t('SpaceModule.manage', 'The member cannot be removed.')]]);
        }

        return ['removed' => true];
    }

    /**
     * Removes the selected members (user ids), each one whose row offers it (not the owner).
     */
    public function actionBulkRemove()
    {
        $space = $this->space();

        return $this->bulkAction(
            'remove',
            $this->findMemberships(...),
            static fn(Membership $membership) => (bool)$space->removeMember($membership->user_id),
            static fn(int $done, int $skipped) => Yii::t('SpaceModule.manage', '{count, plural, =1{1 member was removed} other{# members were removed}}.', ['count' => $done])
                . ($skipped > 0 ? ' ' . Yii::t('SpaceModule.manage', '{count, plural, =1{1 member was left out} other{# members were left out}}.', ['count' => $skipped]) : ''),
        );
    }
}
