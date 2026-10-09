<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\services;

use humhub\components\SettingsManager;
use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\content\models\ContentContainerSetting;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\targets\BaseTarget;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\space\models\Space;
use humhub\modules\space\serializers\SpaceSerializer;
use humhub\modules\user\models\User;
use Yii;

/**
 * The per-channel notification settings of a user, or - without a user - the administrator's
 * defaults for everyone.
 *
 * One key per switchable {@see NotificationCategory} and channel (target id) in the settings of the
 * notification module: `<target id>.category.<category id>`, whether the category reaches the user through
 * the channel, e.g. `mobile.category.social = 0`. A user without an own value inherits the global
 * default; without a global default the category's {@see NotificationCategory::isEnabledByDefault()}
 * decides. A category that is not switchable is always on.
 *
 * @internal
 * @since 1.20
 */
final readonly class NotificationSettingsService
{
    /**
     * @param User|null $user null = the administrator's defaults for everyone
     */
    public function __construct(private ?User $user = null)
    {
    }

    /**
     * True for a category that is not switchable; else the user's switch, inherited from the global
     * default, else the category's default for the channel.
     */
    public function isCategoryEnabled(BaseTarget $target, NotificationCategory $category): bool
    {
        if (!$category->switchable) {
            return true;
        }

        $enabled = $this->get(self::categoryKey($target, $category));

        return $enabled === null ? $category->isEnabledByDefault($target->id) : (bool)$enabled;
    }

    /**
     * Switches the category on or off for the channel; `null` removes the switch of this scope, so
     * the user follows the global default again (the global scope: the category's default).
     */
    public function setCategory(BaseTarget $target, NotificationCategory $category, ?bool $enabled): void
    {
        if ($enabled === null) {
            $this->settings()->delete(self::categoryKey($target, $category));
            return;
        }

        $this->settings()->set(self::categoryKey($target, $category), $enabled ? 1 : 0);
    }

    /**
     * Removes the user's category switches (every `<id>.category.*`) - or, without a user, the global
     * defaults.
     */
    public function reset(): void
    {
        $this->settings()->deleteAll('%.category.%');
    }

    /**
     * Removes every user's category switches and the space selection
     * ({@see NotificationSpaceService::resetSpaces()}, including the "touched settings" flag, so
     * everyone follows the default notification spaces again).
     */
    public static function resetAllUsers(): void
    {
        Yii::$app->db->transaction(function (): void {
            ContentContainerSetting::deleteAll(['AND',
                ['module_id' => 'notification'],
                ['OR',
                    ['LIKE', 'name', '%.category.%', false],
                    ['name' => NotificationSpaceService::IS_TOUCHED_SETTINGS],
                ],
            ]);

            (new NotificationSpaceService())->resetSpaces();
        });

        // ContentContainerSetting::deleteAll() invalidated the caches; drop the managers loaded in this request
        self::moduleSettings()->flushContentContainer();
    }

    /**
     * The settings of this scope as the settings page and `GET /api/v2/notification/settings`
     * show them:
     *
     * - `scope`: `user` or `global`;
     * - `channels`: `[{id, title}]`, the targets active in this scope, the web list first;
     * - `categories`: `[{id, title, description, icon, module, fixed, channels}]`, the categories visible
     *   to the user (all of them for the global defaults) that at least one channel of the scope
     *   applies to, the core categories first; `module` for a
     *   module's own category, `fixed` for a category that is not switchable (`direct`, always on), and
     *   `channels` maps the id of every channel that applies to the category
     *   ({@see BaseTarget::appliesTo()}) to whether the category is on for it;
     * - `defaults`: for a user, what the category switches would be without their own settings -
     *   `{<category id>: {<channel id>: bool}}`, shaped like the `channels` of the categories; `null` for
     *   the global defaults;
     * - `spaces`: `selected` - the spaces whose new content the user is notified about
     *   ({@see NotificationSpaceService::getSpaces()}), for the global defaults the default
     *   notification spaces - as short space shapes, and `enabled` - whether the selection has
     *   a purpose, i.e. the `content` category is among the categories of this scope.
     *
     * @return array{scope: string, channels: array, categories: array, defaults: array|null, spaces: array{selected: array, enabled: bool}}
     */
    public function toArray(): array
    {
        $categories = $this->getCategories();
        $targets = $this->getTargets();
        $defaults = $this->user === null ? null : new self();

        $categoryData = [];
        $defaultData = [];
        foreach ($categories as $category) {
            $switches = [];
            foreach ($targets as $target) {
                if ($target->appliesTo($category)) {
                    $switches[$target->id] = $this->isCategoryEnabled($target, $category);
                    if ($defaults !== null) {
                        $defaultData[$category->id][$target->id] = $defaults->isCategoryEnabled($target, $category);
                    }
                }
            }
            if ($switches === []) {
                // no channel of this scope carries the category
                continue;
            }
            $categoryData[] = [
                'id' => $category->id,
                'title' => $category->title,
                'description' => $category->description,
                'icon' => $category->icon,
                'module' => !$category->isCore(),
                'fixed' => !$category->switchable,
                'channels' => (object)$switches,
            ];
        }

        return [
            'scope' => $this->user === null ? 'global' : 'user',
            'channels' => array_map(fn(BaseTarget $target) => ['id' => $target->id, 'title' => $target->getTitle()], $targets),
            'categories' => $categoryData,
            'defaults' => $defaults === null ? null : (object)array_map(fn(array $switches) => (object)$switches, $defaultData),
            'spaces' => [
                'selected' => array_map(SpaceSerializer::short(...), $this->getSpaces()),
                'enabled' => array_filter($categories, fn(NotificationCategory $category) => $category->id === NotificationCategory::ID_CONTENT) !== [],
            ],
        ];
    }

    /**
     * Applies the settings of the settings page (`PATCH /api/v2/notification/settings`) to this
     * scope. Every part is optional:
     *
     * - `categories`: `{<category id>: {<channel id>: bool|null}}` - a category or channel left out is not
     *   changed, `null` removes the switch of this scope (it follows the default again,
     *   {@see setCategory()}); a category that is not switchable only accepts `true` (and `null`);
     * - `spaces`: the ids of the spaces whose new content notifies the user (of those the user
     *   may see; others are ignored) - the default notification spaces for the global scope.
     *
     * Nothing is written when anything is invalid; the writes run in one transaction. A user
     * whose `spaces` are stored no longer follows the default notification spaces implicitly
     * ({@see NotificationSpaceService::IS_TOUCHED_SETTINGS}); a partial update without `spaces`
     * leaves that untouched.
     *
     * @return array<string, string[]> the errors by field - `categories`, `categories.<category>`,
     * `categories.<category>.<channel>`, `spaces` -, empty on success
     */
    public function fromArray(array $data): array
    {
        $errors = [];
        $switches = [];

        $targets = [];
        foreach ($this->getTargets() as $target) {
            $targets[$target->id] = $target;
        }
        $categories = [];
        foreach ($this->getCategories() as $category) {
            $categories[$category->id] = $category;
        }

        $categorySwitches = $data['categories'] ?? [];
        if (!is_array($categorySwitches) || ($categorySwitches !== [] && array_is_list($categorySwitches))) {
            $errors['categories'][] = Yii::t('NotificationModule.base', 'Invalid categories.');
            $categorySwitches = [];
        }
        foreach ($categorySwitches as $categoryId => $channels) {
            $category = $categories[$categoryId] ?? null;
            if ($category === null) {
                $errors['categories.' . $categoryId][] = Yii::t('NotificationModule.base', 'Unknown category.');
                continue;
            }
            if (!is_array($channels) || ($channels !== [] && array_is_list($channels))) {
                $errors['categories.' . $categoryId][] = Yii::t('NotificationModule.base', 'Invalid value.');
                continue;
            }

            foreach ($channels as $targetId => $value) {
                $field = 'categories.' . $categoryId . '.' . $targetId;
                $target = $targets[$targetId] ?? null;
                if ($target === null || !$target->appliesTo($category)) {
                    $errors[$field][] = Yii::t('NotificationModule.base', 'This channel is not available for the category.');
                    continue;
                }
                $enabled = $value === null || is_bool($value) ? $value : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($value !== null && $enabled === null) {
                    $errors[$field][] = Yii::t('NotificationModule.base', 'Invalid value.');
                    continue;
                }
                if (!$category->switchable) {
                    if ($enabled === false) {
                        $errors[$field][] = Yii::t('NotificationModule.base', 'This category cannot be switched off.');
                    }
                    continue;
                }
                $switches[] = [$target, $category, $enabled];
            }
        }

        $spaceGuids = null;
        if (array_key_exists('spaces', $data)) {
            $ids = is_array($data['spaces']) ? $data['spaces'] : null;
            if ($ids === null || array_filter($ids, fn($id) => !is_int($id) && !(is_string($id) && ctype_digit($id))) !== []) {
                $errors['spaces'][] = Yii::t('NotificationModule.base', 'Invalid spaces.');
            } else {
                $query = Space::find()->andWhere(['space.id' => array_map('intval', $ids)]);
                if ($this->user !== null) {
                    $query->visible($this->user);
                }
                $spaceGuids = $ids === [] ? [] : $query->select('space.guid')->column();
            }
        }

        if ($errors !== []) {
            return $errors;
        }

        try {
            Yii::$app->db->transaction(function () use ($switches, $spaceGuids): void {
                foreach ($switches as [$target, $category, $enabled]) {
                    $this->setCategory($target, $category, $enabled);
                }

                if ($spaceGuids !== null) {
                    if ($this->user !== null) {
                        // The selection is stored: from now on only it counts, the default notification
                        // spaces no longer apply implicitly. Set before the spaces, so they are not added.
                        self::moduleSettings()->user($this->user)->set(NotificationSpaceService::IS_TOUCHED_SETTINGS, true);
                    }
                    (new NotificationSpaceService())->setSpaces($spaceGuids, $this->user);
                }
            });
        } catch (\Throwable $e) {
            // The settings managers cached what was rolled back.
            if ($this->user === null) {
                self::moduleSettings()->reload();
            } else {
                self::moduleSettings()->user($this->user)->reload();
            }
            throw $e;
        }

        return [];
    }

    /**
     * The categories of this scope: the core categories first, then those of modules, each in their order.
     *
     * @return NotificationCategory[]
     */
    private function getCategories(): array
    {
        $categories = Yii::$app->notification->getCategories($this->user);
        usort($categories, fn(NotificationCategory $a, NotificationCategory $b) => $b->isCore() <=> $a->isCore());

        return $categories;
    }

    /**
     * The targets of this scope: the web list first, then the others in their configured order.
     *
     * @return BaseTarget[]
     */
    private function getTargets(): array
    {
        $targets = Yii::$app->notification->getTargets($this->user);
        usort($targets, fn(BaseTarget $a, BaseTarget $b) => ($b instanceof WebTarget) <=> ($a instanceof WebTarget));

        return $targets;
    }

    /**
     * @return Space[]
     */
    private function getSpaces(): array
    {
        if ($this->user === null) {
            $guids = self::moduleSettings()->getSerialized('sendNotificationSpaces');

            return empty($guids) ? [] : Space::find()->where(['guid' => $guids])->orderBy('name')->all();
        }

        $spaces = [];
        foreach ((new NotificationSpaceService())->getSpaces($this->user) as $space) {
            $spaces[$space->id] = $space;
        }

        return array_values($spaces);
    }

    private function get(string $key): mixed
    {
        if ($this->user === null) {
            return self::moduleSettings()->get($key);
        }

        return self::moduleSettings()->user($this->user)->getInherit($key);
    }

    private function settings(): SettingsManager|ContentContainerSettingsManager
    {
        return $this->user === null ? self::moduleSettings() : self::moduleSettings()->user($this->user);
    }

    private static function moduleSettings(): SettingsManager
    {
        return Yii::$app->getModule('notification')->settings;
    }

    private static function categoryKey(BaseTarget $target, NotificationCategory $category): string
    {
        return $target->id . '.category.' . $category->id;
    }
}
