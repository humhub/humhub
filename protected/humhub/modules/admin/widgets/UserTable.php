<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\listing\ListContext;
use humhub\modules\admin\components\UserTable as UserTableDefinition;
use humhub\widgets\VueWidget;
use Yii;
use yii\helpers\Url;

/**
 * The administration's user table (Administration › Users) as a `<data-table>` island: the
 * definitions of {@see UserTableDefinition} (columns, row actions, filters), the card's actions
 * ({@see AdminUserCardActions}, {@see AdminUserCardMenu}) and the rows' endpoint,
 * `GET /api/v2/admin/user`. Until the island mounts, the placeholder shows skeleton rows.
 *
 * @since 1.20
 */
class UserTable extends VueWidget
{
    protected string $component = 'DataTable';

    /**
     * @inheritdoc
     */
    protected function getProps(): array
    {
        return array_merge(Yii::createObject(UserTableDefinition::class)->definitions(ListContext::forCurrentUser()), [
            'tableId' => 'admin-users',
            'label' => Yii::t('AdminModule.user', 'Users'),
            'url' => Url::to(['/admin/api/user/index']),
            'headerActions' => (new AdminUserCardActions())->getEntriesData(),
            'headerMenu' => (new AdminUserCardMenu())->getEntriesData(),
            'labels' => [
                'one' => Yii::t('AdminModule.user', '{count} user'),
                'other' => Yii::t('AdminModule.user', '{count} users'),
                'range' => Yii::t('AdminModule.user', '{from}–{to} of {total} users'),
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getPlaceholder(): string
    {
        return $this->render('userTablePlaceholder');
    }
}
