<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\table\DataTable;
use humhub\modules\admin\components\UserTable as UserTableDefinition;
use humhub\widgets\DataTableWidget;
use humhub\widgets\menu\CardHeaderActions;
use humhub\widgets\menu\DropdownMenu;
use Yii;

/**
 * The administration's user table (Administration › Users) as a `<data-table>` island: the
 * definitions of {@see UserTableDefinition} (columns, row actions, filters), the card's actions
 * ({@see AdminUserCardActions}, {@see AdminUserCardMenu}) and the rows' endpoint,
 * `GET /api/v2/admin/user`.
 *
 * @since 1.20
 */
class UserTable extends DataTableWidget
{
    public const TABLE_ID = 'admin-users';

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
        $this->title = Yii::t('AdminModule.user', 'Overview');
    }

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(UserTableDefinition::class);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        return ['/admin/api/user/index'];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('AdminModule.user', 'Users');
    }

    /**
     * @inheritdoc
     */
    protected function getCountLabels(): array
    {
        return [
            'one' => Yii::t('AdminModule.user', '{count} user'),
            'other' => Yii::t('AdminModule.user', '{count} users'),
            'range' => Yii::t('AdminModule.user', '{from}–{to} of {total} users'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getHeaderActions(): ?CardHeaderActions
    {
        return new AdminUserCardActions();
    }

    /**
     * @inheritdoc
     */
    protected function getHeaderMenu(): ?DropdownMenu
    {
        return new AdminUserCardMenu();
    }
}
