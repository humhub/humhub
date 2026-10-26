<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\components;

use humhub\components\listing\FilterableList;
use humhub\components\table\ColumnDefinition;
use humhub\components\table\DataTable;
use humhub\components\table\RowAction;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;

/**
 * The membership requests of a space (Space › Settings › Members › Pending approvals,
 * `manage\widgets\SpaceApplicantTable`, rows from `GET /api/v2/space/<id>/applicant`): the
 * applicant, their message and when they asked, with "Accept" and "Decline" (also in bulk).
 *
 * A row is a {@see Membership} of the status applicant; its id is the user's id. Created through
 * the DI container with the space, so a module may replace it or change columns and actions on
 * {@see self::EVENT_INIT}.
 *
 * @since 1.20
 */
class SpaceApplicantTable extends DataTable
{
    /**
     * @var Space the space whose membership requests are listed
     */
    public Space $space;

    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new SpaceMemberList(['space' => $this->space, 'status' => Membership::STATUS_APPLICANT]);
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        return [
            new ColumnDefinition('name', ColumnDefinition::TYPE_MAIN, Yii::t('SpaceModule.manage', 'Name'), sort: 'name', sortOrder: 100),
            new ColumnDefinition('message', ColumnDefinition::TYPE_TEXT, Yii::t('SpaceModule.manage', 'Message'), priority: 2, sortOrder: 200),
            new ColumnDefinition('requestedAt', ColumnDefinition::TYPE_DATE, Yii::t('SpaceModule.manage', 'Requested'), priority: 1, sort: 'memberSince', sortOrder: 300, narrow: true),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        $applicant = fn(string $route) => fn(Membership $membership) => [$route, 'id' => $this->space->id, 'userId' => $membership->user_id];

        return [
            new RowAction(
                'accept',
                Yii::t('SpaceModule.manage', 'Accept'),
                RowAction::KIND_REQUEST,
                $applicant('/space/api/applicant/accept'),
                icon: 'check',
                sortOrder: 100,
                bulkUrl: ['/space/api/applicant/bulk', 'id' => $this->space->id, 'action' => 'accept'],
            ),
            new RowAction(
                'view',
                Yii::t('SpaceModule.manage', 'View profile'),
                RowAction::KIND_LINK,
                static fn(Membership $membership) => $membership->user->getUrl(),
                icon: 'eye',
                visible: static fn(Membership $membership) => (int)$membership->user->status === User::STATUS_ENABLED,
                sortOrder: 200,
            ),
            new RowAction(
                'decline',
                Yii::t('SpaceModule.manage', 'Decline'),
                RowAction::KIND_REQUEST,
                $applicant('/space/api/applicant/decline'),
                icon: 'x',
                confirm: Yii::t('SpaceModule.manage', 'Decline the membership request?'),
                destructive: true,
                sortOrder: 100,
                bulkUrl: ['/space/api/applicant/bulk', 'id' => $this->space->id, 'action' => 'decline'],
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var Membership $model */
        return [
            'name' => SpaceMemberTable::nameCell($model->user),
            'message' => $model->request_message ? ['text' => $model->request_message, 'title' => $model->request_message] : null,
            'requestedAt' => $model->created_at ? SpaceMemberTable::dateCell($model->created_at) : null,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function rowId($model): int|string
    {
        return (int)$model->user_id;
    }

    /**
     * Whether the viewer may see the membership requests: the space's administrators.
     */
    public function canAccess(): bool
    {
        return (bool)$this->space->isAdmin();
    }
}
