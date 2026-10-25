<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\table;

use humhub\modules\user\models\User;
use Yii;

/**
 * What a user chose for a data table (the `DataTable` Vue component, by its `tableId`): the
 * optional columns shown, a user setting, so the choice follows the user across devices. Saved
 * by the table through `PUT /api/v2/account/table/<tableId>`, read by the page that mounts it.
 *
 * @since 1.20
 */
class TableSettings
{
    public const TABLE_ID_PATTERN = '/^[a-z0-9][a-z0-9.\-]{0,63}$/';
    public const COLUMN_PATTERN = '/^[A-Za-z0-9_\-]{1,64}$/';
    public const MAX_COLUMNS = 50;

    /**
     * @return string[]|null the keys of the chosen columns; `null` while the user chose none
     *         (the table's defaults apply)
     */
    public static function columns(string $tableId, ?User $user = null): ?array
    {
        $user ??= Yii::$app->user->getIdentity();
        if (!$user instanceof User || !preg_match(self::TABLE_ID_PATTERN, $tableId)) {
            return null;
        }

        $columns = json_decode((string)self::settings($user)->get(self::key($tableId)), true);

        return is_array($columns) ? array_values(array_filter($columns, 'is_string')) : null;
    }

    /**
     * @param string[]|null $columns the chosen columns; `null` returns to the table's defaults
     */
    public static function setColumns(string $tableId, ?array $columns, User $user): void
    {
        if ($columns === null) {
            self::settings($user)->delete(self::key($tableId));
            return;
        }

        self::settings($user)->set(self::key($tableId), json_encode(array_values(array_unique($columns))));
    }

    private static function key(string $tableId): string
    {
        return 'table.' . $tableId . '.columns';
    }

    private static function settings(User $user)
    {
        return Yii::$app->getModule('user')->settings->user($user);
    }
}
