<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\widgets;

use humhub\components\table\DataTable;
use humhub\modules\oauth\components\table\AdminClientTable as AdminClientTableDefinition;
use humhub\widgets\DataTableWidget;
use humhub\widgets\menu\CardHeaderActions;
use Yii;

/**
 * The OAuth clients (Administration › Users › Settings › Apps & API Access › Clients) as a `<data-table>` island ({@see AdminClientTableDefinition}) below the section's tabs, with the
 * rows' endpoint, `GET /api/v2/admin/oauth-client`.
 *
 * @since 1.20
 */
class AdminClientTable extends DataTableWidget
{
    public const TABLE_ID = 'oauth-admin-clients';

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
        return Yii::createObject(AdminClientTableDefinition::class);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        return ['/oauth/api/admin-client/index'];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('OauthModule.base', 'Clients');
    }

    /**
     * @inheritdoc
     */
    protected function getCountLabels(): array
    {
        return [
            'one' => Yii::t('OauthModule.base', '{count} client'),
            'other' => Yii::t('OauthModule.base', '{count} clients'),
            'range' => Yii::t('OauthModule.base', '{from}–{to} of {total} clients'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getHeaderActions(): ?CardHeaderActions
    {
        return new AdminClientCardActions();
    }
}
