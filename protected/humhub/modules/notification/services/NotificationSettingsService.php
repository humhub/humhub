<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\services;

use humhub\components\SettingsManager;
use humhub\modules\activity\components\MailSummary;
use humhub\modules\activity\models\MailSummaryForm;
use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\content\models\ContentContainerSetting;
use humhub\modules\notification\components\NotificationGroup;
use humhub\modules\notification\targets\BaseTarget;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\space\models\Space;
use humhub\modules\space\serializers\SpaceSerializer;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidArgumentException;

/**
 * The per-channel notification settings of a user, or - without a user - the administrator's
 * defaults for everyone.
 *
 * Two kinds of keys in the settings of the notification module, per channel (target id):
 *
 * - `<target id>.mode`: one of the target's {@see BaseTarget::$modes}, e.g. `email.mode = summary`
 * - `<target id>.group.<group id>`: whether a switchable {@see NotificationGroup} reaches the user
 *   through the channel, e.g. `mobile.group.social = 0`
 *
 * A user without an own value inherits the global default; without a global default the mode is
 * the target's first mode and a group is on.
 *
 * @since 1.20
 */
final readonly class NotificationSettingsService
{
    public const MODE_ADAPTIVE = 'adaptive';
    /**
     * Mail only: the activity summary mail carries the notifications.
     */
    public const MODE_SUMMARY = 'summary';
    public const MODE_OFF = 'off';

    /**
     * @param User|null $user null = the administrator's defaults for everyone
     */
    public function __construct(private ?User $user = null)
    {
    }

    /**
     * The channel's mode: the user's setting, else the global default, else the target's first mode.
     * A stored value the target does not (or no longer) support is skipped.
     */
    public function getMode(BaseTarget $target): string
    {
        $key = self::modeKey($target);
        $candidates = [];
        if ($this->user !== null) {
            $candidates[] = self::moduleSettings()->user($this->user)->get($key);
        }
        $candidates[] = self::moduleSettings()->get($key);

        foreach ($candidates as $mode) {
            if (in_array($mode, $target->modes, true)) {
                return $mode;
            }
        }

        return (string)reset($target->modes);
    }

    /**
     * @throws InvalidArgumentException when the mode is not one of the target's modes
     */
    public function setMode(BaseTarget $target, string $mode): void
    {
        if (!in_array($mode, $target->modes, true)) {
            throw new InvalidArgumentException('Mode "' . $mode . '" is not supported by the notification target "' . $target->id . '".');
        }

        $this->settings()->set(self::modeKey($target), $mode);
    }

    /**
     * True for a group that is not switchable; else the user's switch, inherited from the global
     * default, default true.
     */
    public function isGroupEnabled(BaseTarget $target, NotificationGroup $group): bool
    {
        if (!$group->switchable) {
            return true;
        }

        $enabled = $this->get(self::groupKey($target, $group));

        return $enabled === null || (bool)$enabled;
    }

    public function setGroup(BaseTarget $target, NotificationGroup $group, bool $enabled): void
    {
        $this->settings()->set(self::groupKey($target, $group), $enabled ? 1 : 0);
    }

    /**
     * Removes the user's mode and group settings (every `<id>.mode` and `<id>.group.*`) - or, without
     * a user, the global defaults.
     */
    public function reset(): void
    {
        $settings = $this->settings();
        $settings->deleteAll('%.mode');
        $settings->deleteAll('%.group.%');
    }

    /**
     * Removes every user's mode and group settings and the space selection
     * ({@see NotificationSpaceService::resetSpaces()}, including the "touched settings" flag, so
     * everyone follows the default notification spaces again).
     */
    public static function resetAllUsers(): void
    {
        Yii::$app->db->transaction(function (): void {
            ContentContainerSetting::deleteAll(['AND',
                ['module_id' => 'notification'],
                ['OR',
                    ['LIKE', 'name', '%.mode', false],
                    ['LIKE', 'name', '%.group.%', false],
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
     * - `channels`: the web list first (`fixed`, no groups), then every other target active in
     *   this scope with its `mode`, its `modes` (`[{value, label}]`) and its `groups`
     *   (`[{id, title, description, enabled, fixed}]`, the groups visible to the user - all of
     *   them for the global defaults; `direct` is `fixed` and always enabled);
     * - `spaces`: `selected` - the spaces whose new content the user is notified about
     *   ({@see NotificationSpaceService::getSpaces()}), for the global defaults the default
     *   notification spaces - as short space shapes, and `enabled` - whether the selection has
     *   a purpose, i.e. the `content` group is among the groups of this scope;
     * - `summary` (only while the activity module sends summary mails): the `interval` of the
     *   activity summary mail of this scope and the `intervals` (`[{value, label}]`).
     *
     * @return array{channels: array, spaces: array{selected: array, enabled: bool}, summary?: array{interval: int, intervals: array}}
     */
    public function toArray(): array
    {
        $groups = Yii::$app->notification->getGroups($this->user);

        $channels = [];
        foreach ($this->getTargets() as $target) {
            $fixed = $target instanceof WebTarget;
            $channels[] = [
                'id' => $target->id,
                'title' => $target->getTitle(),
                'fixed' => $fixed,
                'mode' => $this->getMode($target),
                'modes' => array_map(fn(string $mode) => ['value' => $mode, 'label' => self::modeLabel($mode)], array_values($target->modes)),
                'groups' => $fixed ? [] : array_map(fn(NotificationGroup $group) => [
                    'id' => $group->id,
                    'title' => $group->title,
                    'description' => $group->description,
                    'enabled' => $this->isGroupEnabled($target, $group),
                    'fixed' => !$group->switchable,
                ], array_values($groups)),
            ];
        }

        $result = [
            'channels' => $channels,
            'spaces' => [
                'selected' => array_map(SpaceSerializer::short(...), $this->getSpaces()),
                'enabled' => array_filter($groups, fn(NotificationGroup $group) => $group->id === NotificationGroup::ID_CONTENT) !== [],
            ],
        ];

        if (self::isSummaryEnabled()) {
            $result['summary'] = [
                'interval' => $this->getSummaryInterval(),
                'intervals' => array_map(
                    fn($value, $label) => ['value' => (int)$value, 'label' => $label],
                    array_keys(self::summaryIntervals()),
                    array_values(self::summaryIntervals()),
                ),
            ];
        }

        return $result;
    }

    /**
     * Applies the settings of the settings page (`PATCH /api/v2/notification/settings`) to this
     * scope. Every part is optional:
     *
     * - `channels`: `[{id, mode, groups: [{id, enabled}]}]` - a channel or group left out is
     *   not changed; the web list (`fixed`) has nothing to change;
     * - `spaces`: the ids of the spaces whose new content notifies the user (of those the user
     *   may see; others are ignored) - the default notification spaces for the global scope;
     * - `summary`: `{interval}`, the activity summary mail interval (ignored while the activity
     *   module sends no summary mails).
     *
     * Nothing is written when anything is invalid; the writes run in one transaction. A user
     * whose `spaces` are stored no longer follows the default notification spaces implicitly
     * ({@see NotificationSpaceService::IS_TOUCHED_SETTINGS}); a partial update without `spaces`
     * leaves that untouched.
     *
     * @return array<string, string[]> the errors by field - `channels`, `<channel>.mode`,
     * `<channel>.group.<group>`, `spaces`, `summary.interval` -, empty on success
     */
    public function fromArray(array $data): array
    {
        $errors = [];
        $modes = [];
        $switches = [];

        $targets = [];
        foreach ($this->getTargets() as $target) {
            $targets[$target->id] = $target;
        }
        $groups = [];
        foreach (Yii::$app->notification->getGroups($this->user) as $group) {
            $groups[$group->id] = $group;
        }

        $channels = $data['channels'] ?? [];
        if (!is_array($channels)) {
            $errors['channels'][] = Yii::t('NotificationModule.base', 'Invalid channels.');
            $channels = [];
        }
        foreach ($channels as $channel) {
            $id = is_array($channel) && is_string($channel['id'] ?? null) ? $channel['id'] : null;
            $target = $id !== null ? ($targets[$id] ?? null) : null;
            if ($target === null) {
                $errors['channels'][] = Yii::t('NotificationModule.base', 'Unknown channel "{id}".', ['id' => is_scalar($id) ? $id : '']);
                continue;
            }

            if (array_key_exists('mode', $channel)) {
                if (!is_string($channel['mode']) || !in_array($channel['mode'], $target->modes, true)) {
                    $errors[$id . '.mode'][] = Yii::t('NotificationModule.base', 'This mode is not available for the channel.');
                } elseif (!$target instanceof WebTarget) {
                    $modes[] = [$target, $channel['mode']];
                }
            }

            foreach (is_array($channel['groups'] ?? null) ? $channel['groups'] : [] as $switch) {
                $groupId = is_array($switch) && is_string($switch['id'] ?? null) ? $switch['id'] : '';
                $group = $groups[$groupId] ?? null;
                if ($group === null || $target instanceof WebTarget) {
                    $errors[$id . '.group.' . $groupId][] = Yii::t('NotificationModule.base', 'Unknown group.');
                    continue;
                }
                $enabled = filter_var($switch['enabled'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($enabled === null) {
                    $errors[$id . '.group.' . $groupId][] = Yii::t('NotificationModule.base', 'Invalid value.');
                    continue;
                }
                if (!$group->switchable) {
                    if (!$enabled) {
                        $errors[$id . '.group.' . $groupId][] = Yii::t('NotificationModule.base', 'This group cannot be switched off.');
                    }
                    continue;
                }
                $switches[] = [$target, $group, $enabled];
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

        $interval = null;
        if (self::isSummaryEnabled() && is_array($data['summary'] ?? null) && array_key_exists('interval', $data['summary'])) {
            $value = $data['summary']['interval'];
            if ((!is_int($value) && !(is_string($value) && ctype_digit($value))) || !array_key_exists((int)$value, self::summaryIntervals())) {
                $errors['summary.interval'][] = Yii::t('NotificationModule.base', 'Invalid interval.');
            } else {
                $interval = (int)$value;
            }
        }

        if ($errors !== []) {
            return $errors;
        }

        try {
            Yii::$app->db->transaction(function () use ($modes, $switches, $spaceGuids, $interval): void {
                foreach ($modes as [$target, $mode]) {
                    $this->setMode($target, $mode);
                }
                foreach ($switches as [$target, $group, $enabled]) {
                    $this->setGroup($target, $group, $enabled);
                }

                if ($spaceGuids !== null) {
                    if ($this->user !== null) {
                        // The selection is stored: from now on only it counts, the default notification
                        // spaces no longer apply implicitly. Set before the spaces, so they are not added.
                        self::moduleSettings()->user($this->user)->set(NotificationSpaceService::IS_TOUCHED_SETTINGS, true);
                    }
                    (new NotificationSpaceService())->setSpaces($spaceGuids, $this->user);
                }

                if ($interval !== null && ($this->user === null || $interval !== $this->getSummaryInterval())) {
                    // A user's own value only when it differs from the inherited one, so a user keeps
                    // following the default until choosing otherwise.
                    $settings = Yii::$app->getModule('activity')->settings;
                    ($this->user === null ? $settings : $settings->user($this->user))->set('mailSummaryInterval', $interval);
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

    private function getSummaryInterval(): int
    {
        $settings = Yii::$app->getModule('activity')->settings;
        $interval = $settings->get('mailSummaryInterval', MailSummary::INTERVAL_DAILY);
        if ($this->user !== null) {
            $interval = $settings->user($this->user)->get('mailSummaryInterval', $interval);
        }

        return (int)$interval;
    }

    private static function isSummaryEnabled(): bool
    {
        return (bool)Yii::$app->getModule('activity')->enableMailSummaries;
    }

    /**
     * @return array<int, string> the labels by interval
     */
    private static function summaryIntervals(): array
    {
        return (new MailSummaryForm())->getIntervals();
    }

    private static function modeLabel(string $mode): string
    {
        return match ($mode) {
            self::MODE_ADAPTIVE => Yii::t('NotificationModule.base', 'Send'),
            self::MODE_SUMMARY => Yii::t('NotificationModule.base', 'Summary only'),
            self::MODE_OFF => Yii::t('NotificationModule.base', 'Off'),
            default => $mode,
        };
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

    private static function modeKey(BaseTarget $target): string
    {
        return $target->id . '.mode';
    }

    private static function groupKey(BaseTarget $target, NotificationGroup $group): string
    {
        return $target->id . '.group.' . $group->id;
    }
}
