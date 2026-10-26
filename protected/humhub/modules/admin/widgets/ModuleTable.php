<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\table\DataTable;
use humhub\modules\admin\components\ModuleTable as ModuleTableDefinition;
use humhub\widgets\DataTableWidget;
use Yii;

/**
 * The administration's module table (Administration › Modules) as a `<data-table>` island
 * ({@see ModuleTableDefinition}) with the rows' endpoint, `GET /api/v2/admin/module`.
 *
 * @since 1.20
 */
class ModuleTable extends DataTableWidget
{
    public const TABLE_ID = 'admin-modules';

    /**
     * @inheritdoc
     */
    public string $tableId = self::TABLE_ID;

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(ModuleTableDefinition::class);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        return ['/admin/api/module/index'];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('AdminModule.base', 'Modules');
    }

    /**
     * @inheritdoc
     */
    protected function getCountLabels(): array
    {
        return [
            'one' => Yii::t('AdminModule.modules', '{count} module'),
            'other' => Yii::t('AdminModule.modules', '{count} modules'),
            'range' => Yii::t('AdminModule.modules', '{from}–{to} of {total} modules'),
        ];
    }
}
