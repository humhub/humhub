<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\table\DataTable;
use humhub\modules\admin\components\ApprovalTable as ApprovalTableDefinition;
use humhub\widgets\DataTableWidget;
use Yii;

/**
 * The registrations awaiting approval (Administration › Users › Pending approvals) as a
 * `<data-table>` island ({@see ApprovalTableDefinition}) with the rows' endpoint,
 * `GET /api/v2/admin/approval`.
 *
 * @since 1.20
 */
class ApprovalTable extends DataTableWidget
{
    public const TABLE_ID = 'admin-approvals';

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
        $this->title = Yii::t('AdminModule.user', 'Pending approvals');
    }

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(ApprovalTableDefinition::class);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        return ['/admin/api/approval/index'];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('AdminModule.user', 'Pending approvals');
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
}
