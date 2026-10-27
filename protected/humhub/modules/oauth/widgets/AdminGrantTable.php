<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\widgets;

use humhub\components\table\DataTable;
use humhub\modules\oauth\components\table\AdminGrantTable as AdminGrantTableDefinition;
use humhub\widgets\DataTableWidget;
use Yii;

/**
 * The grants of all users (Administration › Users › Settings › Apps & API Access › Grants) as a `<data-table>` island ({@see AdminGrantTableDefinition}) below the section's tabs, with the
 * rows' endpoint, `GET /api/v2/admin/oauth-grant`.
 *
 * @since 1.20
 */
class AdminGrantTable extends DataTableWidget
{
    public const TABLE_ID = 'oauth-admin-grants';

    /**
     * @inheritdoc
     */
    public string $tableId = self::TABLE_ID;

    /**
     * @inheritdoc
     */
    public bool $embedded = true;

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(AdminGrantTableDefinition::class);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        return ['/oauth/api/admin-grant/index'];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('OauthModule.base', 'Grants');
    }

    /**
     * @inheritdoc
     */
    protected function getCountLabels(): array
    {
        return [
            'one' => Yii::t('OauthModule.base', '{count} grant'),
            'other' => Yii::t('OauthModule.base', '{count} grants'),
            'range' => Yii::t('OauthModule.base', '{from}–{to} of {total} grants'),
        ];
    }
}
