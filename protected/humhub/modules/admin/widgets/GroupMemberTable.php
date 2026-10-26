<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\table\DataTable;
use humhub\modules\admin\components\GroupMemberTable as GroupMemberTableDefinition;
use humhub\modules\user\models\Group;
use humhub\widgets\DataTableWidget;
use humhub\widgets\menu\CardHeaderActions;
use Yii;

/**
 * The members of a group (Administration › Groups › Members) as a `<data-table>` island
 * ({@see GroupMemberTableDefinition}) inside the group's card, with "Add members"
 * ({@see GroupMemberCardActions}) and the rows' endpoint, `GET /api/v2/admin/group/<id>/member`.
 *
 * @since 1.20
 */
class GroupMemberTable extends DataTableWidget
{
    public const TABLE_ID = 'admin-group-members';

    /**
     * @inheritdoc
     */
    public string $tableId = self::TABLE_ID;

    /**
     * @inheritdoc
     */
    public bool $embedded = true;

    public Group $group;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->title = Yii::t('AdminModule.user', 'Members');
    }

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(['class' => GroupMemberTableDefinition::class, 'group' => $this->group]);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        return ['/admin/api/group-member/index', 'id' => $this->group->id];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('AdminModule.user', 'Members of {group}', ['group' => $this->group->name]);
    }

    /**
     * @inheritdoc
     */
    protected function getCountLabels(): array
    {
        return [
            'one' => Yii::t('AdminModule.user', '{count} member'),
            'other' => Yii::t('AdminModule.user', '{count} members'),
            'range' => Yii::t('AdminModule.user', '{from}–{to} of {total} members'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getHeaderActions(): ?CardHeaderActions
    {
        /** @var GroupMemberTableDefinition $table */
        $table = $this->getTable();

        return new GroupMemberCardActions(['group' => $this->group, 'canAdd' => $table->canModifyMembers()]);
    }
}
