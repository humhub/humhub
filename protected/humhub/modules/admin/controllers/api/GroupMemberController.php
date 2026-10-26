<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers\api;

use humhub\components\api\DataTableController;
use humhub\components\table\DataTable;
use humhub\modules\admin\components\GroupMemberTable;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The members of a group as rows of the member table (`GroupMemberTable`):
 * `GET admin/group/<id>/member` answers one page of them (the list's filters and sorts as query
 * parameters) in the paginated envelope; `POST admin/group/<id>/member/<userId>/manager|member`
 * makes a member a group manager or a plain member again and answers the new row;
 * `POST admin/group/<id>/member/<userId>/remove` removes a member (`{removed: true}`);
 * `POST admin/group/<id>/member/bulk/remove` removes the selected members (`ids`, or `all` the
 * members of the list's filters in the query).
 *
 * For those who may manage the group ({@see GroupMemberTable::canAccess()}); each change only
 * where the member's row offers it.
 *
 * @since 1.20
 */
class GroupMemberController extends DataTableController
{
    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        $group = Group::findOne(['id' => (int)Yii::$app->request->get('id')]);
        if ($group === null) {
            throw new NotFoundHttpException(Yii::t('AdminModule.user', 'Group not found!'));
        }

        return Yii::createObject(['class' => GroupMemberTable::class, 'group' => $group]);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'actions' => [
                    'manager' => ['POST'],
                    'member' => ['POST'],
                    'remove' => ['POST'],
                    'bulk-remove' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionManager($userId)
    {
        return $this->setManager((int)$userId, 'manager', true);
    }

    public function actionMember($userId)
    {
        return $this->setManager((int)$userId, 'member', false);
    }

    public function actionRemove($userId)
    {
        $groupUser = $this->findMember((int)$userId, 'remove');

        if (!$this->group()->removeUser($groupUser->user_id)) {
            return $this->validationErrors(['userId' => [Yii::t('AdminModule.user', 'The user cannot be removed from this Group!')]]);
        }

        return ['removed' => true];
    }

    /**
     * Removes the selected members (user ids), each one whose row offers it.
     */
    public function actionBulkRemove()
    {
        $group = $this->group();

        return $this->bulkAction(
            'remove',
            static fn(array $ids) => GroupUser::find()->where(['group_id' => $group->id, 'user_id' => $ids]),
            static fn(GroupUser $groupUser) => (bool)$group->removeUser($groupUser->user_id),
            static fn(int $done, int $skipped) => Yii::t('AdminModule.user', '{count, plural, =1{1 member was removed} other{# members were removed}}.', ['count' => $done])
                . ($skipped > 0 ? ' ' . Yii::t('AdminModule.user', '{count, plural, =1{1 member was left out} other{# members were left out}}.', ['count' => $skipped]) : ''),
        );
    }

    /**
     * @inheritdoc
     * Without the group's id of the route, which Yii puts among the query parameters.
     */
    protected function listParams(): array
    {
        return array_diff_key(parent::listParams(), ['id' => true]);
    }

    private function group(): Group
    {
        /** @var GroupMemberTable $table */
        $table = $this->getTable();

        return $table->group;
    }

    /**
     * The member whose row offers the action `$actionId`.
     */
    private function findMember(int $userId, string $actionId): GroupUser
    {
        $groupUser = GroupUser::findOne(['group_id' => $this->group()->id, 'user_id' => $userId]);
        if ($groupUser === null) {
            throw new NotFoundHttpException(Yii::t('AdminModule.user', 'Group user not found!'));
        }

        $action = $this->getTable()->getAction($actionId);
        if ($action === null || !$action->isVisibleFor($groupUser)) {
            throw new ForbiddenHttpException();
        }

        return $groupUser;
    }

    private function setManager(int $userId, string $actionId, bool $manager): array
    {
        $groupUser = $this->findMember($userId, $actionId);
        $groupUser->is_group_manager = $manager;
        if (!$groupUser->save()) {
            return $this->validationErrors($groupUser);
        }

        return ['row' => $this->getTable()->row($groupUser)];
    }
}
