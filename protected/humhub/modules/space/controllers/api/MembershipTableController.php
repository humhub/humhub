<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers\api;

use humhub\components\api\DataTableController;
use humhub\components\table\DataTable;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The endpoint of a table of a space's memberships (its members, invitations or membership
 * requests): the space from the route's `id`, the table created through the DI container with
 * it, and the membership of a row whose action is called.
 *
 * @since 1.20
 */
abstract class MembershipTableController extends DataTableController
{
    /**
     * @return string the table's class, a {@see DataTable} with a `space` property
     */
    abstract protected function tableClass(): string;

    /**
     * @return int the status of the table's memberships
     */
    abstract protected function membershipStatus(): int;

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        $space = Space::findOne(['id' => (int)Yii::$app->request->get('id')]);
        if ($space === null) {
            throw new NotFoundHttpException(Yii::t('SpaceModule.base', 'Space not found!'));
        }

        return Yii::createObject(['class' => $this->tableClass(), 'space' => $space]);
    }

    /**
     * @inheritdoc
     * Without the space's id and the bulk action of the route, which Yii puts among the query
     * parameters.
     */
    protected function listParams(): array
    {
        return array_diff_key(parent::listParams(), ['id' => true, 'action' => true]);
    }

    protected function space(): Space
    {
        return $this->getTable()->space;
    }

    /**
     * The memberships of the table's status with the given user ids.
     */
    protected function findMemberships(array $userIds)
    {
        return Membership::find()->where([
            'space_id' => $this->space()->id,
            'status' => $this->membershipStatus(),
            'user_id' => $userIds,
        ]);
    }

    /**
     * The membership of a row offering the action `$actionId`.
     */
    protected function findMembership(int $userId, string $actionId): Membership
    {
        $membership = $this->findMemberships([$userId])->one();
        if ($membership === null) {
            throw new NotFoundHttpException(Yii::t('SpaceModule.manage', 'Could not find membership!'));
        }

        $action = $this->getTable()->getAction($actionId);
        if ($action === null || !$action->isVisibleFor($membership)) {
            throw new ForbiddenHttpException();
        }

        return $membership;
    }
}
