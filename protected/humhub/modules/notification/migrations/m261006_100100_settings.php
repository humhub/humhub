<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Migration;
use yii\db\Query;

/**
 * Migrates the notification settings of 1.19 - one switch per category and channel,
 * `notification.<category>_<target>` - to the channel modes and group switches of 1.20
 * (`<target>.mode`, `<target>.group.<group>`), for the global defaults (`setting`) and for every
 * user (`contentcontainer_setting`).
 *
 * In 1.19 a switch was the user's key, else the global key, else the category's default (on,
 * except likes by e-mail). The migration evaluates that effective value of every mapped
 * category:
 *
 * - a channel whose every mapped category is off is `off`, else `adaptive`;
 * - a group (other than `direct`, which cannot be switched off) whose every mapped category is
 *   off is switched off, else on.
 *
 * The global result is stored where it differs from the 1.20 defaults (`adaptive`, on); a user's
 * result only where it differs from the new global result - in both directions, so a user who
 * had a channel on under a global "off" keeps it on. Categories of modules are not mapped and
 * fall back to the defaults, `web` switches are dropped (the web list is always on); all old
 * keys are deleted.
 *
 * Plain queries only: the models and settings managers of the time this runs may differ.
 *
 * @since 1.20
 */
class m261006_100100_settings extends Migration
{
    /**
     * The core categories of 1.19 and the group each one became.
     */
    public const CATEGORY_GROUPS = [
        'mentioned' => 'direct',
        'friendship' => 'direct',
        'space_member' => 'direct',
        'comments' => 'social',
        'like' => 'social',
        'followed' => 'social',
        'content_created' => 'content',
        'space_created' => 'admin',
        'admin' => 'admin',
    ];

    /**
     * The channels whose settings are migrated.
     */
    public const TARGETS = ['email', 'mobile'];

    /**
     * The 1.19 defaults that were not "on" (`LikeNotificationCategory::getDefaultSetting()`), by key.
     */
    public const OFF_BY_DEFAULT = ['notification.like_email'];

    /**
     * Categories 1.19 stored only for users who saw them (`space_created`: `ManageSpaces`) - not
     * in the administrator's defaults, not for other users. Without a key in the chain (user,
     * global) such a category is left out of the evaluation, so the `admin` group is decided by
     * `admin_<target>` alone.
     */
    public const STORED_ONLY_WHEN_VISIBLE = ['space_created'];

    private const MODULE_ID = 'notification';
    private const OLD_PREFIX = 'notification.';
    private const BATCH_SIZE = 500;

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $global = (new Query())
            ->select(['value', 'name'])
            ->from('setting')
            ->where(['module_id' => self::MODULE_ID])
            ->andWhere(['LIKE', 'name', self::OLD_PREFIX . '%', false])
            ->indexBy('name')
            ->column($this->db);

        $this->migrateGlobal($global);
        $this->migrateUsers($global);

        $this->delete('setting', ['AND', ['module_id' => self::MODULE_ID], ['LIKE', 'name', self::OLD_PREFIX . '%', false]]);
        $this->delete('contentcontainer_setting', ['AND', ['module_id' => self::MODULE_ID], ['LIKE', 'name', self::OLD_PREFIX . '%', false]]);

        Yii::$app->cache->flush();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m261006_100100_settings cannot be reverted.\n";

        return false;
    }

    /**
     * @param array<string, string|null> $global the old global settings by name
     */
    private function migrateGlobal(array $global): void
    {
        $existing = (new Query())
            ->select('name')
            ->from('setting')
            ->where(['module_id' => self::MODULE_ID])
            ->andWhere(['OR', ['LIKE', 'name', '%.mode', false], ['LIKE', 'name', '%.group.%', false]])
            ->column($this->db);

        foreach (self::diff(self::resolve($global, []), self::defaults()) as $name => $value) {
            if (!in_array($name, $existing, true)) {
                $this->insert('setting', ['module_id' => self::MODULE_ID, 'name' => $name, 'value' => $value]);
            }
        }
    }

    /**
     * @param array<string, string|null> $global the old global settings by name
     */
    private function migrateUsers(array $global): void
    {
        $globalResult = self::resolve($global, []);

        $containerIds = (new Query())
            ->select('contentcontainer_id')
            ->distinct()
            ->from('contentcontainer_setting')
            ->where(['module_id' => self::MODULE_ID])
            ->andWhere(['LIKE', 'name', self::OLD_PREFIX . '%', false])
            ->orderBy('contentcontainer_id')
            ->column($this->db);

        foreach (array_chunk($containerIds, self::BATCH_SIZE) as $chunk) {
            $rows = (new Query())
                ->select(['contentcontainer_id', 'name', 'value'])
                ->from('contentcontainer_setting')
                ->where(['module_id' => self::MODULE_ID, 'contentcontainer_id' => $chunk])
                ->all($this->db);

            $old = [];
            $existing = [];
            foreach ($rows as $row) {
                $id = (int)$row['contentcontainer_id'];
                if (str_starts_with((string)$row['name'], self::OLD_PREFIX)) {
                    $old[$id][$row['name']] = $row['value'];
                } else {
                    $existing[$id][$row['name']] = true;
                }
            }

            $insert = [];
            foreach ($old as $id => $settings) {
                foreach (self::diff(self::resolve($settings, $global), $globalResult) as $name => $value) {
                    if (!isset($existing[$id][$name])) {
                        $insert[] = [self::MODULE_ID, $id, $name, $value];
                    }
                }
            }

            if ($insert !== []) {
                $this->batchInsert('contentcontainer_setting', ['module_id', 'contentcontainer_id', 'name', 'value'], $insert);
            }
        }
    }

    /**
     * The 1.20 settings resulting from the effective 1.19 switches of a scope: its own keys, else
     * those of `$base` (the global keys for a user), else the category's default.
     *
     * @param array<string, string|null> $keys the old settings of the scope by name
     * @param array<string, string|null> $base the old settings it inherits from by name
     * @return array<string, string> every new key (`<target>.mode`, `<target>.group.<group>`) with its value
     */
    public static function resolve(array $keys, array $base): array
    {
        $result = [];
        foreach (self::TARGETS as $target) {
            $groups = [];
            foreach (self::CATEGORY_GROUPS as $category => $group) {
                $name = self::OLD_PREFIX . $category . '_' . $target;
                $value = $keys[$name] ?? $base[$name] ?? null;
                if ($value === null && in_array($category, self::STORED_ONLY_WHEN_VISIBLE, true)) {
                    // Never shown, never stored: it does not count either way.
                    continue;
                }
                $groups[$group][] = $value === null ? !in_array($name, self::OFF_BY_DEFAULT, true) : (bool)$value;
            }

            $result[$target . '.mode'] = in_array(true, array_merge(...array_values($groups)), true) ? 'adaptive' : 'off';
            foreach ($groups as $group => $enabled) {
                if ($group !== 'direct') {
                    $result[$target . '.group.' . $group] = in_array(true, $enabled, true) ? '1' : '0';
                }
            }
        }

        return $result;
    }

    /**
     * The 1.20 defaults: every channel `adaptive`, every group on.
     *
     * @return array<string, string>
     */
    private static function defaults(): array
    {
        $defaults = [];
        foreach (self::TARGETS as $target) {
            $defaults[$target . '.mode'] = 'adaptive';
            foreach (array_unique(self::CATEGORY_GROUPS) as $group) {
                if ($group !== 'direct') {
                    $defaults[$target . '.group.' . $group] = '1';
                }
            }
        }

        return $defaults;
    }

    /**
     * @return array<string, string> the entries of `$result` whose value differs from `$reference`
     */
    private static function diff(array $result, array $reference): array
    {
        return array_filter($result, fn($value, $name) => ($reference[$name] ?? null) !== $value, ARRAY_FILTER_USE_BOTH);
    }
}
