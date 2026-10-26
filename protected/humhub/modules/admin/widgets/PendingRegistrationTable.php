<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\table\DataTable;
use humhub\modules\admin\components\PendingRegistrationTable as PendingRegistrationTableDefinition;
use humhub\widgets\DataTableWidget;
use humhub\widgets\menu\DropdownMenu;
use Yii;

/**
 * The pending registrations (Administration › Users › Pending registrations) as a `<data-table>`
 * island ({@see PendingRegistrationTableDefinition}), a sub-page of the user overview, with the
 * exports ({@see PendingRegistrationCardMenu}) and the rows' endpoint, `GET /api/v2/admin/invite`.
 *
 * @since 1.20
 */
class PendingRegistrationTable extends DataTableWidget
{
    public const TABLE_ID = 'admin-pending-registrations';

    /**
     * @inheritdoc
     */
    public string $tableId = self::TABLE_ID;

    /**
     * @inheritdoc
     */
    public bool $selectByDefault = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->title = Yii::t('AdminModule.base', 'Pending user registrations');
        $this->backUrl = ['/admin/user/index'];
        $this->backLabel = Yii::t('AdminModule.base', 'Back to user overview');
    }

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(PendingRegistrationTableDefinition::class);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        return ['/admin/api/invite/index'];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('AdminModule.base', 'Pending user registrations');
    }

    /**
     * @inheritdoc
     */
    protected function getCountLabels(): array
    {
        return [
            'one' => Yii::t('AdminModule.user', '{count} registration'),
            'other' => Yii::t('AdminModule.user', '{count} registrations'),
            'range' => Yii::t('AdminModule.user', '{from}–{to} of {total} registrations'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getHeaderMenu(): ?DropdownMenu
    {
        return new PendingRegistrationCardMenu();
    }
}
