<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components\table;

use humhub\components\listing\FilterableList;
use humhub\components\table\ColumnDefinition;
use humhub\components\table\DataTable;
use humhub\components\table\RowAction;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\oauth\models\Client;
use Yii;
use yii\helpers\Url;

/**
 * The OAuth clients (Administration › Users › Settings › Apps & API Access › Clients,
 * `oauth\widgets\AdminClientTable`, rows from `GET /api/v2/admin/oauth-client`): name with the
 * client id, type, status and when created, with edit and delete — not on the HumHub app.
 *
 * Created through the DI container, so a module may replace it or change columns and actions on
 * {@see self::EVENT_INIT}.
 *
 * @since 1.20
 */
class AdminClientTable extends DataTable
{
    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new AdminClientList();
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        return [
            new ColumnDefinition('name', ColumnDefinition::TYPE_MAIN, Yii::t('OauthModule.base', 'Name'), sort: 'name', sortOrder: 100),
            new ColumnDefinition('type', ColumnDefinition::TYPE_CHIP, Yii::t('OauthModule.base', 'Type'), priority: 2, sort: 'type', sortOrder: 200),
            new ColumnDefinition('status', ColumnDefinition::TYPE_CHIP, Yii::t('OauthModule.base', 'Status'), priority: 1, sort: 'status', filter: 'status', sortOrder: 300, narrow: true),
            new ColumnDefinition('createdAt', ColumnDefinition::TYPE_DATE, Yii::t('OauthModule.base', 'Created'), priority: 3, sort: 'createdAt', sortOrder: 400),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        $editable = static fn(Client $client) => $client->type !== Client::TYPE_SYSTEM;

        return [
            new RowAction(
                'edit',
                Yii::t('OauthModule.base', 'Edit'),
                RowAction::KIND_LINK,
                static fn(Client $client) => ['/oauth/admin/client', 'id' => $client->id],
                icon: 'pencil',
                visible: $editable,
                sortOrder: 100,
            ),
            new RowAction(
                'delete',
                Yii::t('OauthModule.base', 'Delete'),
                RowAction::KIND_POST,
                static fn(Client $client) => ['/oauth/admin/delete-client', 'id' => $client->id],
                icon: 'trash',
                confirm: Yii::t('OauthModule.base', 'Delete this client? All its grants are revoked.'),
                destructive: true,
                visible: $editable,
                sortOrder: 100,
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var Client $model */
        return [
            'name' => [
                'text' => $model->name,
                'subtitle' => $model->client_id,
                'url' => $model->type !== Client::TYPE_SYSTEM ? Url::to(['/oauth/admin/client', 'id' => $model->id]) : null,
            ],
            'type' => [['text' => $model->type === Client::TYPE_SYSTEM
                ? Yii::t('OauthModule.base', 'HumHub app')
                : Yii::t('OauthModule.base', 'Administrator')]],
            'status' => [$model->is_active
                ? ['text' => Yii::t('OauthModule.base', 'Active'), 'value' => AdminClientList::STATUS_ACTIVE, 'tone' => 'success']
                : ['text' => Yii::t('OauthModule.base', 'Inactive'), 'value' => AdminClientList::STATUS_INACTIVE]],
            'createdAt' => self::dateCell($model->created_at),
        ];
    }

    /**
     * Whether the viewer may see the clients: those who may manage the settings.
     */
    public function canAccess(): bool
    {
        return Yii::$app->user->can(ManageSettings::class);
    }

    /**
     * A date cell of a Unix timestamp, or none.
     */
    public static function dateCell(?int $timestamp): ?array
    {
        return $timestamp === null ? null : [
            'value' => Yii::$app->formatter->asDatetime($timestamp, 'php:c'),
            'text' => Yii::$app->formatter->asDate($timestamp),
            'title' => Yii::$app->formatter->asDatetime($timestamp),
        ];
    }
}
