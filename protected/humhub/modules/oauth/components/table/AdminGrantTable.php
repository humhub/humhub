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
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\Grant;
use Yii;
use yii\helpers\Url;

/**
 * The grants of all users (Administration › Users › Settings › Apps & API Access › Grants,
 * `oauth\widgets\AdminGrantTable`, rows from `GET /api/v2/admin/oauth-grant`): the user, the
 * type, the app or token and when last used, on request the scopes, the expiry and when created;
 * with "Revoke", also in bulk.
 *
 * Created through the DI container, so a module may replace it or change columns and actions on
 * {@see self::EVENT_INIT}.
 *
 * @since 1.20
 */
class AdminGrantTable extends DataTable
{
    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new AdminGrantList();
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        return [
            new ColumnDefinition('user', ColumnDefinition::TYPE_MAIN, Yii::t('OauthModule.base', 'User'), sort: 'user', sortOrder: 100),
            new ColumnDefinition('type', ColumnDefinition::TYPE_CHIP, Yii::t('OauthModule.base', 'Type'), priority: 2, sort: 'type', filter: 'type', sortOrder: 200),
            new ColumnDefinition('name', ColumnDefinition::TYPE_TEXT, Yii::t('OauthModule.base', 'Name / client'), priority: 1, sortOrder: 300),
            new ColumnDefinition('scopes', ColumnDefinition::TYPE_CHIP, Yii::t('OauthModule.base', 'Scopes'), priority: 3, sortOrder: 350, visible: false),
            new ColumnDefinition('lastUsed', ColumnDefinition::TYPE_DATE, Yii::t('OauthModule.base', 'Last used'), priority: 2, sort: 'lastUsed', sortOrder: 400),
            new ColumnDefinition('expires', ColumnDefinition::TYPE_DATE, Yii::t('OauthModule.base', 'Expires'), priority: 3, sort: 'expires', sortOrder: 500, visible: false),
            new ColumnDefinition('createdAt', ColumnDefinition::TYPE_DATE, Yii::t('OauthModule.base', 'Created'), priority: 3, sort: 'createdAt', sortOrder: 600, visible: false),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        return [
            new RowAction(
                'revoke',
                Yii::t('OauthModule.base', 'Revoke'),
                RowAction::KIND_REQUEST,
                static fn(Grant $grant) => ['/oauth/api/admin-grant/revoke', 'id' => $grant->id],
                icon: 'trash',
                confirm: Yii::t('OauthModule.base', 'Revoke this grant?'),
                destructive: true,
                sortOrder: 100,
                bulkUrl: ['/oauth/api/admin-grant/bulk-revoke'],
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var Grant $model */
        $user = $model->user;
        $never = ['text' => Yii::t('OauthModule.base', 'Never')];

        return [
            'user' => $user === null ? ['text' => '—'] : [
                'text' => $user->displayName,
                'subtitle' => $user->email,
                'image' => $user->getProfileImage()->getUrl(),
                'url' => Url::to(['/admin/user/edit', 'id' => $user->id]),
            ],
            'type' => [['text' => AdminGrantList::types()[$model->type] ?? $model->type, 'value' => $model->type]],
            'name' => ['text' => $model->client?->name ?? $model->name],
            // A scope of a disabled module is shown by its id
            'scopes' => array_map(static fn(string $id) => ['text' => ScopeRegistry::get($id)?->label ?? $id], $model->getScopeIds()),
            'lastUsed' => AdminClientTable::dateCell($model->last_used_at) ?? $never,
            'expires' => AdminClientTable::dateCell($model->expires_at) ?? $never,
            'createdAt' => AdminClientTable::dateCell($model->created_at),
        ];
    }

    /**
     * Whether the viewer may see the grants: those who may manage the settings.
     */
    public function canAccess(): bool
    {
        return Yii::$app->user->can(ManageSettings::class);
    }
}
