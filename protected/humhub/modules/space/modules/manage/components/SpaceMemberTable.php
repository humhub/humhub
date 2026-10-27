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
 * The members of a space (Space › Settings › Members, `manage\widgets\SpaceMemberTable`, rows from
 * `GET /api/v2/space/<id>/member`): name, role, since when and the last visit, with the actions
 * the space's administrators may take on each — view the profile, give another role and remove
 * from the space; not on the owner, whose role follows the ownership.
 *
 * A row is a {@see Membership}; its id is the user's id. Created through the DI container with the
 * space, so a module may replace it or change columns and actions on {@see self::EVENT_INIT}.
 *
 * @since 1.20
 */
class SpaceMemberTable extends DataTable
{
    /**
     * @var Space the space whose members are listed
     */
    public Space $space;

    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new SpaceMemberList(['space' => $this->space]);
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        return [
            new ColumnDefinition('name', ColumnDefinition::TYPE_MAIN, Yii::t('SpaceModule.manage', 'Name'), sort: 'name', sortOrder: 100),
            new ColumnDefinition('role', ColumnDefinition::TYPE_CHIP, Yii::t('SpaceModule.manage', 'Role'), priority: 1, sort: 'role', filter: 'role', sortOrder: 200),
            new ColumnDefinition('memberSince', ColumnDefinition::TYPE_DATE, Yii::t('SpaceModule.manage', 'Member since'), priority: 2, sort: 'memberSince', sortOrder: 300),
            new ColumnDefinition('lastVisit', ColumnDefinition::TYPE_DATE, Yii::t('SpaceModule.manage', 'Last visit'), priority: 3, sort: 'lastVisit', sortOrder: 400),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        $member = fn(string $route, array $params = []) => fn(Membership $membership) => [$route, 'id' => $this->space->id, 'userId' => $membership->user_id] + $params;

        $actions = [
            new RowAction(
                'view',
                Yii::t('SpaceModule.manage', 'View profile'),
                RowAction::KIND_LINK,
                static fn(Membership $membership) => $membership->user->getUrl(),
                icon: 'eye',
                visible: static fn(Membership $membership) => (int)$membership->user->status === User::STATUS_ENABLED,
                sortOrder: 100,
            ),
        ];

        $labels = [
            Space::USERGROUP_ADMIN => [Yii::t('SpaceModule.manage', 'Make administrator'), 'shield-check'],
            Space::USERGROUP_MODERATOR => [Yii::t('SpaceModule.manage', 'Make moderator'), 'shield'],
            Space::USERGROUP_MEMBER => [Yii::t('SpaceModule.manage', 'Make member'), 'user'],
        ];
        $sortOrder = 200;
        foreach (Membership::getAssignableUserGroups() as $role) {
            [$label, $icon] = $labels[$role] ?? [Yii::t('SpaceModule.manage', 'Change role'), 'user'];
            $actions[] = new RowAction(
                'role-' . $role,
                $label,
                RowAction::KIND_REQUEST,
                $member('/space/api/member/role', ['role' => $role]),
                icon: $icon,
                visible: fn(Membership $membership) => $membership->group_id !== $role && !$this->isOwner($membership),
                sortOrder: $sortOrder++,
            );
        }

        $actions[] = new RowAction(
            'remove',
            Yii::t('SpaceModule.manage', 'Remove from space'),
            RowAction::KIND_REQUEST,
            $member('/space/api/member/remove'),
            icon: 'user-minus',
            confirm: Yii::t('SpaceModule.manage', 'Are you sure you want to remove this member.'),
            destructive: true,
            visible: fn(Membership $membership) => !$this->isOwner($membership),
            sortOrder: 100,
            bulkUrl: ['/space/api/member/bulk-remove', 'id' => $this->space->id],
        );

        return $actions;
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var Membership $model */
        return [
            'name' => self::nameCell($model->user),
            'role' => [$this->roleCell($model)],
            'memberSince' => $model->created_at ? self::dateCell($model->created_at) : null,
            'lastVisit' => $model->last_visit
                ? self::dateCell($model->last_visit)
                : ['text' => Yii::t('SpaceModule.manage', 'never')],
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
     * Whether the viewer may see the members: the space's administrators.
     */
    public function canAccess(): bool
    {
        return (bool)$this->space->isAdmin();
    }

    private function isOwner(Membership $membership): bool
    {
        return (bool)$this->space->isSpaceOwner($membership->user_id);
    }

    /**
     * The member's role as a chip setting the `role` filter; the owner's without, as no filter
     * value names it.
     */
    private function roleCell(Membership $membership): array
    {
        if ($this->isOwner($membership)) {
            return ['text' => Yii::t('SpaceModule.base', 'Owner'), 'tone' => 'info'];
        }

        $roles = SpaceMemberList::roles();

        return [
            'text' => $roles[$membership->group_id] ?? (string)$membership->group_id,
            'value' => (string)$membership->group_id,
            'tone' => $membership->group_id === Space::USERGROUP_MEMBER ? null : 'info',
        ];
    }

    /**
     * The main cell of a member's row: name, job title or e-mail, image and the profile's link.
     */
    public static function nameCell(User $user): array
    {
        return [
            'text' => $user->displayName,
            'subtitle' => $user->displayNameSub ?: $user->email,
            'image' => $user->getProfileImage()->getUrl(),
            'url' => (int)$user->status === User::STATUS_ENABLED ? $user->getUrl() : null,
        ];
    }

    /**
     * A date cell: the date, the date and time as tooltip.
     */
    public static function dateCell(string $value): array
    {
        return [
            'value' => Yii::$app->formatter->asDatetime($value, 'php:c'),
            'text' => Yii::$app->formatter->asDate($value),
            'title' => Yii::$app->formatter->asDatetime($value),
        ];
    }
}
