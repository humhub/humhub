# Notifications

Notifications tell one or more specific users that something concerns them — a mention, a
comment on their post, an invitation. Compare with [activities](concept-activities.md), which
are bound to a container and not addressed to anybody in particular.

A notification is written once per recipient and reaches the user through **channels**: the web
list (the bell in the top bar and `/notification/overview`), e-mail, mobile push, and whatever a
module adds. Today every channel receives a notification as soon as it is written: one mail or
push per notification. A **delivery layer** that decides *when* a mail or push goes out — at once
when the user is quiet, collected into one message when many arrive, never when the user has
already seen the notification — follows in the next update; see [Delivery](#delivery) below and
the [administrator's guide](../admin/notifications.md).

Core examples:

- `humhub\modules\user\notifications\MentionedNotification` — a user was mentioned
- `humhub\modules\comment\notifications\NewCommentNotification` — a new comment on followed content
- `humhub\modules\like\notifications\NewLikeNotification` — somebody liked a post or comment
- `humhub\modules\content\notifications\ContentCreatedNotification` — new content in a space the user receives notifications for

## Implementing a notification

### 1. The notification class

Place it under your module's `notifications/` directory and extend `BaseNotification`. A class
must provide two things: the sentence, and the group it belongs to:

```php
namespace johndoe\example\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
use Yii;

final class SomethingHappenedNotification extends BaseNotification
{
    public static function group(): NotificationGroup
    {
        return NotificationGroup::social();
    }

    protected function getMessage(array $params): string
    {
        return Yii::t('ExampleModule.notifications', '{displayName} did something cool.', $params);
    }
}
```

There are no view files. The same `getMessage()` renders the entry in the web list, the mail,
its plain-text version and the push body; only `$params` differs per channel — in the web list
and the HTML mail the names are bold, in plain text and push they are not. The parameters:

| Parameter | Meaning |
|---|---|
| `displayName` | the originator |
| `displayNames` | the originators of a grouped notification: "Anna, Bob and 2 more" (empty for a single one) |
| `groupCount` | how many notifications the entry stands for (1 when ungrouped) |
| `namedCount` | how many people `displayNames` names: 0 when empty, 1 when the group collapses to one person (e.g. one originator's notifications), 2 for "Anna and Bob" as well as "Anna, Bob and 2 more" — use the plural sentence only when it is 2 |
| `content` | type and preview of the content: *post "Release notes"* (only when the source is a content; "[Deleted]" when the content's record is gone) |
| `contentTitle` | the preview alone ("[Deleted]" as well when the record is gone) |

The object is bound to its database record and exposes what the sentence usually needs:

| Property | |
|---|---|
| `$record` | the `Notification` row |
| `$recipient` | the user this notification is for |
| `$originator` | the user who caused it, `null` for system notifications |
| `$content` | the related `Content`, when the source is a content or a comment |
| `$contentContainer` | the content's container, or the container itself when it is the source |
| `$sourceRecord` | any other source record: a comment, a group, a membership request |
| `$payload` | the array given at dispatch |
| `$groupCount` | see above |

What you may override:

| Method | Default |
|---|---|
| `getMailSubject(): string` | the plain-text sentence |
| `getUrl(bool $scheme = false): ?string` | the content addon's URL (e.g. the comment's), else the content's, else the container's, else none |
| `getMailActions(): array` | one "View online" button; return `[['label' => …, 'url' => …], …]` |
| `getMailBody(): ?string` | `null`; a plain-text block shown under the sentence in mails, e.g. a message the originator wrote; encoded by the view |
| `getMailContentRecord(): ?ContentOwner` | the content record, rendered as a preview under the sentence in the mail |
| `getSpace(): ?Space` | the container when it is a space; override when the notification is about a space without being bound to it, e.g. a membership |
| `canReceive(User $user): bool` | `true`; a last filter at dispatch time beyond the generic checks |
| `getGroupingQuery(): ?ActiveQueryNotification` | `null`, no grouping — see [Grouping](#4-grouping) |

A notification without an originator (a system message) simply never gets one.

### 2. Group, priority, listing

Every notification belongs to a **group**, which is what the user and the administrator switch
on or off per channel. `group()` is where a class says in one line where it belongs. There is
no class to write for it.

The core has four fixed groups; most notifications of a small module fit one of them:

| Group | Contains | Priority | Switchable |
|---|---|---|---|
| `direct` | mentions, invitations, requests, messages | high | no |
| `social` | reactions to my content: comments, likes, follows | low (comments: normal) | yes |
| `content` | new content in the spaces the user selected | normal | yes |
| `admin` | administration | normal | yes, shown to administrators only |

A task assigned to the user is a direct notification, a reaction to the user's content is
social, and so on. A module whose notifications deserve a switch of their own — the messenger,
a task manager — returns its module group instead:

```php
public static function group(): NotificationGroup
{
    return NotificationGroup::ofModule(self::class);
}
```

That is one switch "Example" under e-mail and under push, named after the module, with nothing
else to declare; it appears on the settings page as soon as a class uses it. A module that
wants several switches returns its own `new NotificationGroup('example-reports', $title,
$description)` from the classes concerned. Groups are compared by id.

The **priority** says how urgently a notification should go out: `high` at once, `normal` after
the adaptive delay, `low` with the next mail that goes out anyway. It defaults to the group's;
override `priority()` to return a `NotificationPriority` when a single class differs. It is
stored with every notification and handed to push providers (`DeliveryBatch::isHighPriority()`);
the delays themselves come with the delivery layer — until then every priority goes out at once.

`listed()` says whether the notification appears in the web list. It is `true` unless a module
has its own list for these entries — the messenger shows messages in its inbox, so its
notifications return `false` and only exist for mail and push.

### 3. Dispatching

```php
use humhub\modules\notification\components\NotificationManager;

NotificationManager::dispatch(
    SomethingHappenedNotification::class,
    $recipients,            // a User, an array of users or ids, or an ActiveQueryUser
    $source,                // a content record, a container or any ActiveRecord; optional
    $originator,            // optional
    ['payload' => ['count' => 3]],
);
```

`dispatch()` returns immediately; a queued job fans out to the recipients. Per recipient the job
skips users who are not enabled, the originator (unless `notifyOriginator` is set), users who
block or are blocked by the originator, users who may not see the content (`Content::canView()`,
for a content source), and whoever `canReceive()` rejects. A container source alone filters
nobody: an invited user is not a member of the space yet. A recipient who already has a
notification of the same class, source and originator is skipped too, so dispatching twice never
doubles an entry. What remains is written, grouped, listed, announced through the live system and
handed to every other channel enabled for the recipient.

Options: `payload` (array), `notifyOriginator` (default `false`), `priority`
(`NotificationPriority`), `dedupe` (default `true`). Switch `dedupe` off for notifications that
report an event that can legitimately repeat (an invitation accepted twice after leaving); keep it
for ones that represent a pending state (an open invitation).

`NotificationManager::EVENT_BEFORE_DISPATCH` fires once per dispatch with a
`BeforeDispatchEvent` carrying the class, recipients, source, originator and options; a handler
may change them or set `$event->isValid = false`.

The conventional place to dispatch is `afterSave()` of the source record, once per state
transition: the record exists when the job runs, and it fires exactly once.

To take a notification back when its cause is undone (a like removed, a mention edited away):

```php
NotificationManager::delete(SomethingHappenedNotification::class, $source, $user);
```

Deleting the source record deletes its notifications through the foreign keys. A module that
shows notifications in its own list marks them as seen when the user looks:

```php
NotificationManager::markSeen(NewMessageNotification::class, $conversation, $user);
```

With the delivery layer, this will also cancel a mail or push that has not gone out yet.

### 4. Grouping

Twelve uploaded files, five likes on the same post, three comments under one article: a
notification class declares which of its notifications belong together, and the recipient sees
one entry ("Anna created 12 new files", "Anna, Bob and 3 more like your post") and receives one
mail. Return the query that finds the siblings of a new notification:

```php
public function getGroupingQuery(): ?ActiveQueryNotification
{
    return Notification::find()
        ->andWhere(['notification.class' => self::class])
        ->andWhere(['notification.content_id' => $this->content->id]);
}
```

The core narrows it to the recipient and to a time bucket and groups when enough notifications
match — both are properties of your class: `$groupingTimeBucketSeconds` (900, 15 minutes) and
`$groupingThreshold` (2). Grouping happens when a notification is written, so the list reads the
groups as they are stored. The grouped sentence is yours: branch on `$groupCount` in
`getMessage()` and use `displayNames`. A group is one entry in the list, a mail or push sent
for a grouped notification carries the grouped sentence, and marking it as seen marks every
member.

### 5. Mail

A mail is rendered from the sentence, the optional body (`getMailBody()`), the optional content
preview (`getMailContentRecord()`) and the buttons (`getMailActions()`) inside the standard
layout (`@notification/views/mails/notification` and its plain-text twin). The view already
renders a batch of several notifications as one mail; with the delivery layer, notifications due
at the same time will share one. Nothing in your module renders mail.

The mail mode `summary` (*Summary only*) — notifications only in a block at the top of the
activity summary mail instead of separate mails — comes with the delivery layer, together with
that block. Until then the mail channel offers `adaptive` and `off` only; `BaseTarget::isEnabled()`
already treats `summary` like `off`, and a stored `summary` value is ignored (the global default,
or else `adaptive`, applies).

## Delivery

**Today.** The dispatch job writes the notification, sends the live event and then hands it to
every channel enabled for the recipient (`BaseTarget::isEnabled()`), at once and synchronously,
as a `DeliveryBatch` holding this one notification. A failing channel is logged (category
`notification`) and does not stop the others.

**With the delivery layer (next update).** The web list and the live event stay immediate. For
every other channel the dispatch job will record a *delivery* with a due time in a
`notification_delivery` table, and a queued job will send what is due:

- **Adaptive delay.** The first mail in a quiet hour goes out at once, the next one after five
  minutes, the one after that after fifteen, every further one after thirty — the numbers are
  the channel's `delays`, counted over `delayWindow`. A `high` notification always goes at once,
  a `low` one waits at least `lowPriorityDelay`.
- **Collecting.** A notification whose recipient already has a delivery waiting on that channel
  rides along with it: the user gets one mail listing both.
- **Skipping.** Right before sending, a delivery is dropped when the notification was seen in
  the meantime, when the channel is no longer enabled for it, or (`skipWhenOnline`, on for mail)
  when the user is online.

`delays`, `delayWindow`, `lowPriorityDelay` and `skipWhenOnline` are already properties of every
channel, reserved for the delivery layer; they have no effect yet. A module never deals with
any of this; it dispatches, the rest follows from the group, the priority and the user's
settings — so nothing changes for a module when the delivery layer arrives.

## Channels (targets)

A channel is a `BaseTarget` configured under `components.notification.targets` by its id. The
core ships `web`, `email` and `mobile`; a module adds one by registering its class there:

```php
// config.php of the module, or the installation's config/common.php
'components' => [
    'notification' => [
        'targets' => [
            'chat' => ['class' => \johndoe\chat\components\ChatTarget::class],
        ],
    ],
],
```

```php
final class ChatTarget extends BaseTarget
{
    public string $id = 'chat';
    public array $modes = [self::MODE_ADAPTIVE, self::MODE_OFF];

    public function getTitle(): string
    {
        return Yii::t('ChatModule.base', 'Chat');
    }

    public function deliver(DeliveryBatch $batch): void
    {
        // $batch->recipient, $batch->notifications (readonly properties), $batch->first(),
        // $batch->isSingle(), $batch->getSubject(), $batch->getPushBody(), $batch->getUrl(),
        // $batch->getCollapseKey(), $batch->isHighPriority(), $batch->getUnreadCount()
    }
}
```

The target inherits the delay properties (reserved for the delivery layer) and the per-user
mode and group switches; it appears on the settings page as a section of its own. The first of
`$modes` is the default. Override `isActive(?User $user)` (calling the parent) when the channel
is not available to everyone. Push providers implement `MobileTargetProvider` — `isActive()` and
the same `deliver(DeliveryBatch)` — and register it in the DI container under that interface.

The properties `$id` (`string`), `$active` (`bool`) and `$modes` (`array`) are typed; a subclass
redeclares them with the same types.

## Settings

Users set, per channel, a **mode** — `adaptive` (the default, labelled *Send*) or `off`
(*Off*); the mail mode `summary` (*Summary only*) comes with the delivery layer — and switch the groups other than `direct` on or off, under
*Account settings → Notifications*. Administrators set the defaults under *Administration →
Settings → Notifications* and can reset every user to them (*Reset for all users*, shown to
administrators who may also manage users). Which spaces send *new content* notifications is a
separate choice on the same page, and so is the interval of the activity summary mail while
summary mails are enabled.

The keys, in the notification module's settings (global defaults) and user settings:
`<channel>.mode` and `<channel>.group.<group id>` (`email.mode`, `mobile.group.social`,
`email.group.example`). A user without an own value inherits the global default; without one the
mode is the channel's first mode and a group is on. `NotificationSettingsService` reads and
writes them.

A module that needs to know whether a user wants a kind of notification on a channel asks the
target:

```php
Yii::$app->notification->getTarget('email')->isEnabled(NewMessageNotification::class, $user);
```

## API and live updates

- `GET /api/v2/notification` — the caller's own list, one entry per group, newest group first,
  cursor-paged (`cursor`, `limit`, `groups[]`, `seen`); `POST
  /api/v2/notification/mark-as-seen` — by `ids[]` (their whole groups) or all. Shapes in
  `notification\serializers\NotificationSerializer`.
- `GET`/`PATCH /api/v2/notification/settings` — the settings page's data
  (`NotificationSettingsService::toArray()`/`fromArray()`); `?scope=global` for the
  administrator's defaults. `POST /api/v2/notification/settings/reset` resets the caller,
  `POST /api/v2/notification/settings/reset-all` every user (`ManageSettings` and `ManageUsers`). The pages `/notification/user` and
  `/notification/admin/defaults` are the `NotificationSettings` island
  (`notification\widgets\SettingsPage`).
- The live event `humhub\modules\notification\live\NewNotification` is sent for every listed
  notification; the `NotificationMenu` island refreshes its count and list from it.

## Migrating from 1.19

The 1.19 API — `SocialActivity`, the builder `instance()->from()->about()->send()`, `html()`,
`viewName`, view files, notification categories — is gone. The
[module migration guide](module-migrate-1.20.md) lists every replacement; the short version: a
notification class is constructed from its record, writes its sentence in `getMessage()`, is
dispatched through `NotificationManager::dispatch()`, and names its group in one line instead
of a category class.
