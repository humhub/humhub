<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\table\DataTable;
use humhub\modules\admin\components\GroupTable as GroupTableDefinition;
use humhub\widgets\DataTableWidget;
use humhub\widgets\menu\CardHeaderActions;
use Yii;

/**
 * The administration's group table (Administration › Users › Groups) as a `<data-table>` island
 * ({@see GroupTableDefinition}), with "Create new group" ({@see AdminGroupCardActions}) and the
 * rows' endpoint, `GET /api/v2/admin/group`.
 *
 * @since 1.20
 */
class GroupTable extends DataTableWidget
{
    public const TABLE_ID = 'admin-groups';

    /**
     * @inheritdoc
     */
    public string $tableId = self::TABLE_ID;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->title = Yii::t('AdminModule.user', 'Groups');
    }

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(GroupTableDefinition::class);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        return ['/admin/api/group/index'];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('AdminModule.user', 'Groups');
    }

    /**
     * @inheritdoc
     */
    protected function getCountLabels(): array
    {
        return [
            'one' => Yii::t('AdminModule.user', '{count} group'),
            'other' => Yii::t('AdminModule.user', '{count} groups'),
            'range' => Yii::t('AdminModule.user', '{from}–{to} of {total} groups'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getHeaderActions(): ?CardHeaderActions
    {
        return new AdminGroupCardActions();
    }
}
