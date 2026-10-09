<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Migration;
use yii\db\Query;

/**
 * Migrates the notification settings of 1.19 - one switch per (old) category and channel,
 * `notification.<old category>_<target>` - to the category switches of 1.20
 * (`<target>.category.<category>`), for the global defaults (`setting`) and for every user
 * (`contentcontainer_setting`). The nine core categories of 1.19 are merged into the five
 * core categories of 1.20.
 *
 * In 1.19 a switch was the user's key, else the global key, else the old category's default (on,
 * except likes by e-mail). For every channel, a category (other than `direct`, which cannot be
 * switched off) with at least one stored key among its mapped old categories is switched off when
 * the effective value of every mapped old category is off, else on. A category none of whose
 * old categories has a stored key gets no key: it follows the 1.20 default
 * ({@see \humhub\modules\notification\components\NotificationCategory::isEnabledByDefault()}),
 * e.g. new followers by e-mail are off.
 *
 * The global result is stored where it differs from the 1.20 defaults; a user's result only
 * where it differs from the new effective global setting - in both directions, so a user who
 * had a category on under a global "off" keeps it on. Old categories of modules are not mapped and
 * fall back to the defaults; all old keys are deleted.
 *
 * Plain queries only: the models and settings managers of the time this runs may differ.
 *
 * @since 1.20
 */
class m261006_100100_settings extends Migration
{
    /**
     * The core categories of 1.19 and the 1.20 category each one became.
     */
    public const CATEGORY_MAP = [
        'mentioned' => 'direct',
        'friendship' => 'direct',
        'space_member' => 'direct',
        'comments' => 'social',
        'like' => 'social',
        'followed' => 'followers',
        'content_created' => 'content',
        'space_created' => 'admin',
        'admin' => 'admin',
    ];

    /**
     * The channels whose settings are migrated.
     */
    public const TARGETS = ['web', 'email', 'mobile'];

    /**
     * The 1.20 category switches that are off by default (`NotificationCategory::$offByDefault`).
     */
    public const NEW_OFF_BY_DEFAULT = ['email.category.followers', 'mobile.category.followers'];

    /**
     * The 1.19 defaults that were not "on" (`LikeNotificationCategory::getDefaultSetting()`), by key.
     */
    public const OFF_BY_DEFAULT = ['notification.like_email'];

    /**
     * Categories 1.19 stored only for users who saw them (`space_created`: `ManageSpaces`) - not
     * in the administrator's defaults, not for other users. Without a key in the chain (user,
     * global) such a category is left out of the evaluation, so the `admin` category is decided by
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
            ->andWhere(['LIKE', 'name', '%.category.%', false])
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
        $globalResult = array_merge(self::defaults(), self::resolve($global, []));

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
     * those of `$base` (the global keys for a user), else the old category's default. A 1.20
     * category with no stored key in the chain is left out.
     *
     * @param array<string, string|null> $keys the old settings of the scope by name
     * @param array<string, string|null> $base the old settings it inherits from by name
     * @return array<string, string> the new keys (`<target>.category.<category>`) with their value
     */
    public static function resolve(array $keys, array $base): array
    {
        $result = [];
        foreach (self::TARGETS as $target) {
            $categories = [];
            $stored = [];
            foreach (self::CATEGORY_MAP as $oldCategory => $category) {
                if ($category === 'direct') {
                    continue;
                }
                $name = self::OLD_PREFIX . $oldCategory . '_' . $target;
                $value = $keys[$name] ?? $base[$name] ?? null;
                if ($value === null && in_array($oldCategory, self::STORED_ONLY_WHEN_VISIBLE, true)) {
                    // Never shown, never stored: it does not count either way.
                    continue;
                }
                $stored[$category] = ($stored[$category] ?? false) || $value !== null;
                $categories[$category][] = $value === null ? !in_array($name, self::OFF_BY_DEFAULT, true) : (bool)$value;
            }

            foreach ($categories as $category => $enabled) {
                if ($stored[$category]) {
                    $result[$target . '.category.' . $category] = in_array(true, $enabled, true) ? '1' : '0';
                }
            }
        }

        return $result;
    }

    /**
     * The 1.20 defaults of the mapped categories: on, except {@see NEW_OFF_BY_DEFAULT}.
     *
     * @return array<string, string>
     */
    private static function defaults(): array
    {
        $defaults = [];
        foreach (self::TARGETS as $target) {
            foreach (array_unique(self::CATEGORY_MAP) as $category) {
                if ($category !== 'direct') {
                    $name = $target . '.category.' . $category;
                    $defaults[$name] = in_array($name, self::NEW_OFF_BY_DEFAULT, true) ? '0' : '1';
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
