# Notifications

Notifications tell one or more specific users that something concerns them — a mention, a
comment on their post, an invitation. Compare with [activities](concept-activities.md), which
are bound to a container and not addressed to anybody in particular.

A notification is written once per recipient and reaches the user through **channels**: the web
list (the bell in the top bar and `/notification/overview`), e-mail, mobile push, and whatever a
module adds. The web list receives a notification as soon as it is written. For mail and push a
**delivery layer** decides *when* a message goes out — at once when the user is quiet, collected
into one message when many arrive, never when the user has already seen the notification; see
[Delivery](#delivery) below and the [administrator's guide](../admin/notifications.md).

Core examples:

- `humhub\modules\user\notifications\MentionedNotification` — a user was mentioned
- `humhub\modules\comment\notifications\NewCommentNotification` — a new comment on followed content
- `humhub\modules\like\notifications\NewLikeNotification` — somebody liked a post or comment
- `humhub\modules\content\notifications\ContentCreatedNotification` — new content in a space the user receives notifications for

## Implementing a notification

A notification is one class in the module's `notifications/` directory. It extends
`BaseNotification`, writes its sentence in `getMessage()` and is sent with `send()`. Everything
else has a default and is overridden only when needed.

### A complete example

A task module notifies the assignees of a task. The task is no content, so it is the source
record of the notification; the module wants the assignments of one task grouped, the task
title highlighted and the assigner's note shown under the sentence:

```php
namespace johndoe\tasks\notifications;

use humhub\components\message\MessageParam;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\Grouping;
use humhub\modules\notification\components\NotificationBlock;
use humhub\modules\notification\components\NotificationContext;
use johndoe\tasks\models\Task;
use Yii;

final class TaskAssignedNotification extends BaseNotification
{
    public static function grouping(): ?Grouping
    {
        // "Anna and Ben assigned you to “Release 2.0”"
        return Grouping::bySource();
    }

    protected function getMessage(array $params): string
    {
        return Yii::t(
            'TasksModule.notifications',
            '{groupCount, plural, =1{{displayName}} other{{displayNames}}} assigned you to {task}.',
            $params,
        );
    }

    protected function getMessageParams(): array
    {
        return ['task' => MessageParam::emphasis($this->getTask()?->title ?? '', 100)];
    }

    public function getUrl(bool $scheme = false): ?string
    {
        return $this->getTask()?->getUrl($scheme);
    }

    public function getBlocks(NotificationContext $context): array
    {
        // shown under the sentence in a mail about this notification; a "View online" button follows
        $note = trim($this->payload['note'] ?? '');

        return $note !== '' ? [NotificationBlock::quote($note, $this->originator, $this->record->created_at)] : [];
    }

    private function getTask(): ?Task
    {
        return $this->sourceRecord instanceof Task ? $this->sourceRecord : null;
    }
}
```

Sending and revoking it:

```php
// Task::afterSave(), when assignees were added
TaskAssignedNotification::send($newAssignees, source: $this, originator: Yii::$app->user->identity, payload: ['note' => $note]);

// when an assignee is removed again
TaskAssignedNotification::revoke(source: $this, user: $assignee);
```

There is no category to declare: the notification gets the module's own category — one switch
"Tasks" per channel on the settings page — unless the class names another one, see
[Category, priority, listing](#category-priority-listing). There are no view files either: the
same `getMessage()` renders the web list, the HTML mail, the text mail, the mail subject and the
push message, and what a mail shows below the sentence is declared as
[content blocks](#content-blocks).

### The sentence and its parameters

`getMessage(array $params)` returns the sentence, usually `Yii::t(…, $params)`. The parameters
are rendered for the channel: HTML-encoded (with names in `<strong>`) for the web list and the
HTML mail, plain text for the text mail, the mail subject and push. The built-in ones:

| Parameter | Meaning |
|---|---|
| `displayName` | the originator (`''` without one) |
| `displayNames` | the originators of a grouped notification: "Anna, Bob and 2 more" (empty for a single one) |
| `groupCount` | how many notifications the entry stands for (1 when ungrouped) |
| `namedCount` | how many people `displayNames` names: 0 when empty, 1 when the group collapses to one person (e.g. one originator's notifications), 2 for "Anna and Bob" as well as "Anna, Bob and 2 more" |
| `content` | type and preview of the `getSubjectRecord()`: *post "Release notes"* (only for a notification about a content; "[Deleted]" when the content's record is gone) |
| `contentTitle` | the preview alone ("[Deleted]" as well when the record is gone) |

`getMessageParams()` adds the class's own parameters (and may override built-in ones). A value
is a plain string — plain text, encoded for HTML — or a `humhub\components\message\MessageParam`:

| Factory | HTML | Plain text |
|---|---|---|
| `MessageParam::text($text, ?$maxLength)` | encoded | as is |
| `MessageParam::emphasis($text, ?$maxLength)` | encoded in `<strong>` | in “quotes” |
| `MessageParam::user($user)` | the display name, encoded in `<strong>` | the display name |

`$maxLength` cuts the text (with an ellipsis) before it is encoded. Numbers stay numbers, for
ICU `plural` and `select`.

When the sentence differs only by grouping, prefer one ICU message to branching in PHP — it
keeps the translations together:

```php
// the same verb: pick the subject
'{groupCount, plural, =1{{displayName}} other{{displayNames}}} commented {content}.'
// a verb that agrees with the number of people
'{namedCount, plural, =2{{displayNames} like} other{{displayName} likes}} {content}.'
```

Branching on `$this->groupCount` in `getMessage()` and returning two messages is valid as well.

`getMailSubject(array $params)` is the subject of a mail about this notification alone, from the
plain-text parameters; the sentence by default.

### What the object knows

The object is bound to its database record and exposes what the sentence usually needs as
readonly properties:

| Property | |
|---|---|
| `$record` | the `Notification` row |
| `$recipient` | the user this notification is for |
| `$originator` | the user who caused it, `null` for system notifications |
| `$content` | the related `Content`, when the source is a content or a content addon |
| `$contentContainer` | the content's container, or the container itself when it is the source |
| `$sourceRecord` | any other source record: a comment, a group, a task |
| `$payload` | the `payload` given to `send()` |
| `$groupCount` | see above |

### Hooks

| Method | Default |
|---|---|
| `static category(): NotificationCategory` | the module's own category, `NotificationCategory::ofModule(static::class)` |
| `static priority(): NotificationPriority` | the category's priority |
| `static listed(): bool` | `true`: shown in the web list |
| `static grouping(): ?Grouping` | `null`, no grouping — see [Grouping](#grouping) |
| `getMessageParams(): array` | `[]` |
| `getMailSubject(array $params): string` | the sentence |
| `getUrl(bool $scheme = false): ?string` | the content addon's URL (e.g. the comment's), else the content's, else the container's, else none |
| `getSubjectRecord(): ?ContentOwner` | the content's record — what `content`/`contentTitle` name; e.g. the liked comment instead |
| `getBlocks(NotificationContext $context): NotificationBlock[]` | the preview of the subject record (when there is one) and a "View online" button — everything shown below the sentence, see [Content blocks](#content-blocks) |
| `static standalone(): bool` | `false`; `true`: always delivered as a message of its own, see [Content blocks](#content-blocks) |
| `getSpace(): ?Space` | the container when it is a space; override when the notification is about a space without being bound to it, e.g. a membership |
| `canReceive(User $user): bool` | `true`; a last filter at dispatch time beyond the generic checks |

### Category, priority, listing

Every notification belongs to a **category**, which is what the user and the administrator
switch on or off per channel. By default that is the module's own category
(`NotificationCategory::ofModule()`): id = module id, title = module name, icon `ti-puzzle`. It
appears on the settings page under *From modules* as soon as a class uses it.

The core has five categories; a notification that fits one of them names it in `category()`:

| Category | Contains | Priority | Switchable | Icon | Off by default |
|---|---|---|---|---|---|
| `direct` | mentions, invitations, requests, membership and role changes, deleted content | high | no | `ti-at` | — |
| `social` | reactions to my content: comments, likes | low (comments: normal) | yes | `ti-heart` | — |
| `followers` | new followers | low | yes | `ti-user-plus` | `email`, `mobile` |
| `content` | new content in the spaces the user selected | normal | yes | `ti-news` | — |
| `admin` | administration | normal | yes, shown to users who may manage settings, users or spaces | `ti-shield` | — |

```php
public static function category(): NotificationCategory
{
    return NotificationCategory::direct();
}
```

To give the module's category an icon, return `NotificationCategory::ofModule(self::class,
'ti-checklist')`. A module that needs several switches constructs its categories, with ids
prefixed by its module id:

```php
public static function category(): NotificationCategory
{
    return new NotificationCategory(
        'tasks-reminders',
        Yii::t('TasksModule.base', 'Task reminders'),
        Yii::t('TasksModule.base', 'Reminders of tasks that are due.'),
        icon: 'ti-alarm',
        offByDefault: [MobileTarget::ID],
        permissions: [ManageUsers::class],
    );
}
```

Categories are compared by id. A switchable category is on for every channel except those in its
`offByDefault` (`isEnabledByDefault($channelId)`); the administrator's defaults and the user's
own switches override that, see [Settings](#settings). `permissions` lists global permission
classes of which a user needs any to see the category on the settings page (none: everyone);
space permissions are not supported. The channel ids are the constants `WebTarget::ID` (`web`),
`MailTarget::ID` (`email`) and `MobileTarget::ID` (`mobile`).

The **priority** says how urgently a notification should go out: `High` at once, `Normal` after
the adaptive delay, `Low` with the next mail that goes out anyway. It defaults to the category's;
override `priority()` when a single class differs. It is a property of the notification type, not
of a single `send()`: a notification that is sometimes urgent and sometimes not is two classes.
It is stored with every notification, used
by the delivery layer and handed to push providers (`DeliveryBatch::isHighPriority()`).

`listed()` says whether the notification appears in the web list. It is `true` unless a module
has its own list for these entries — the messenger shows messages in its inbox, so its
notifications return `false` and only exist for mail and push. A category has a web switch only
when at least one of its classes is listed (`WebTarget::appliesTo()`).

The web list is a channel like the others: a listed notification whose category the user
switched off for the web is still written when another channel delivers it, but stored with
`listed = 0` — it never shows in the list or the badge and sends no live event. When no channel
at all takes a notification for a recipient, no record is written.

### Sending

```php
SomethingHappenedNotification::send(
    $recipients,              // a User, an id, an array of users or ids, or an ActiveQueryUser
    source: $record,          // a content, a content record, a content addon, a container or any saved record; optional
    originator: $user,        // optional
    payload: ['count' => 3],  // optional, see $payload
);
```

The other named arguments: `notifyOriginator` (default `false`) and `dedupe` (default `true`).

`send()` returns immediately; a queued job fans out to the recipients. Inside a transaction of
`Yii::$app->db` the job is queued after the outermost commit, and dropped on a rollback
(`humhub\components\db\AfterCommit`), so a worker never runs it before the records it refers to
are committed. Per recipient the job skips users who are not enabled, the originator (unless
`notifyOriginator`), users who block or are blocked by the originator, users who may not see the
content (`Content::canView()`, for a content source), and whoever `canReceive()` rejects. A
container source alone filters nobody: an invited user is not a member of the space yet. A
recipient who already has a notification of the same class, source and originator is skipped
too (`dedupe`), so sending twice never doubles an entry. Switch `dedupe` off for notifications
that report an event that can legitimately repeat (an invitation accepted twice after leaving);
keep it for ones that represent a pending state (an open invitation). What remains is written,
grouped, listed, announced through the live system and scheduled for every other channel
enabled for the recipient.

`NotificationManager::EVENT_BEFORE_DISPATCH` fires once per `send()` with a
`BeforeDispatchEvent` carrying `class`, `recipients`, `source`, `originator`, `payload`,
`notifyOriginator` and `dedupe`; a handler may change them or set
`$event->isValid = false`.

The conventional place to send is `afterSave()` of the source record, once per state
transition: the record exists when the job runs, and it fires exactly once.

### Revoking and marking seen

To take notifications back when their cause is undone (a like removed, an assignee removed):

```php
SomethingHappenedNotification::revoke(source: $record, user: $user, originator: $originator); // each optional
```

When the source record itself is deleted, its notifications go with it:

- a **content** — with the notifications about its comments and other addons — when it is
  soft-deleted (`Content::softDeleteInternal()`), and a **container** through the foreign keys
  of the `notification` table;
- a **content addon** (e.g. a comment) and **any other record** that extends
  `humhub\components\ActiveRecord` through its `record_map` row, which the core deletes when the
  record is deleted with `delete()`.

A record removed without its delete events — `deleteAll()`, plain SQL — leaves its
notifications behind: revoke them first (`MyNotification::revoke(source: $record)`). Otherwise
they linger until the list or the delivery finds them unloadable and drops them.

A module that shows notifications in its own list marks them as seen when the user looks:

```php
// the messenger, when the user opens, answers or leaves a conversation
MailNotification::markSeen($conversation, $user);
```

This marks the whole groups and also cancels a mail or push that has not gone out yet.

### Grouping

Twelve uploaded files, five likes on the same post, three comments under one article: a class
declares which of its notifications belong together, and the recipient sees one entry ("Anna
created 12 new files", "Anna, Bob and 3 more like your post") and receives one mail.
`grouping()` returns a `Grouping`:

| Factory | Groups the notifications … |
|---|---|
| `Grouping::byContent()` | about the same content (also about its comments) |
| `Grouping::bySource()` | about the same source record other than a content or a container |
| `Grouping::byContainer()` | about the same space or profile |
| `Grouping::byClass()` | of the class, whatever they are about |

The factory names what must be present and the same; a notification without it (no content for
`byContent()`) is not grouped. The modifiers narrow it further, each returning a new instance:
`andContent()`, `andSource()` and `andOriginator()` (the same value, none matching none),
`andContentType()` (a content of the same type), `unseenOnly()` (a new notification after the
user saw the group starts a new one), `withThreshold(int)` (the minimum size of a group, 2) and
`withTimeBucket(int $seconds)` (only notifications created in the same bucket, 900 = 15
minutes). The core always adds the class and the recipient.

The core notifications:

| Class | Grouping |
|---|---|
| `NewCommentNotification` | `Grouping::byContent()` |
| `NewLikeNotification` | `Grouping::byContent()->andSource()` — the likes of one content or one comment |
| `ContentCreatedNotification` | `Grouping::byContainer()->andOriginator()->andContentType()` |
| `FollowedNotification` | `Grouping::byClass()` |

Grouping happens when a notification is written, so the list reads the groups as they are
stored. A group is one entry in the list, a mail or push sent for a grouped notification carries
the grouped sentence, and marking it as seen marks every member.

### Content blocks

Everything shown below the sentence is a list of **content blocks**, returned by
`getBlocks(NotificationContext $context)` — link buttons included. A block is a
`humhub\modules\notification\components\NotificationBlock`; it says *what* it holds, the
channel decides how it looks and how much of it fits. There are no styling options.

| Factory | HTML mail | Text mail |
|---|---|---|
| `heading($text)` | a heading, encoded | the text |
| `text($text)` | a paragraph, encoded, line breaks kept | the text |
| `richText($richText)` | HumHub RichText (Markdown) converted for mails (`RichTextToEmailHtmlConverter` with the recipient, so images open from the inbox; images limited to the mail's width) | converted to plain text (`RichTextToPlainTextConverter`) |
| `quote($text, ?$author, ?$date)` | a card with the author's image, name and date and the encoded text; without an author a plain card | `Author:` and the lines prefixed with `> ` |
| `contentPreview($record)` | the preview card of a content or a content addon, like on the stream (`MailContentEntry`) | its text as plain text |
| `button($label, $url)` | a button; consecutive buttons form one row | `label: URL`, one line each |
| `html($html, $text)` | `$html` as is — the escape hatch for what the other blocks cannot express; encode its contents and keep it mail-safe (tables, inline styles) | `$text` |

The default is the preview of `getSubjectRecord()` (when there is one) and a "View online"
button, which opens `getUrl()` through the entry URL (marking the notification seen). When a
class returns blocks without any button, the core appends that "View online" button anyway, so
a mail always links to its notification; a class adds its own buttons to replace it. A
notification without a URL (`getUrl()` returns `null`, e.g. about deleted content) gets no
button at all.

```php
// a news article: the full text, and a button that belongs to it
public function getBlocks(NotificationContext $context): array
{
    $news = $this->getNews();

    return [
        NotificationBlock::heading($news->title),
        NotificationBlock::richText($news->article),
        NotificationBlock::button(Yii::t('NewsModule.base', 'Confirm reading'), $news->getConfirmReadingUrl(true)),
    ];
}
```

Where blocks appear: today only a mail about this notification alone renders them. The web list,
push messages and a mail of several notifications show the sentence (and in a mail a link to the
notification) only — so a button like "Confirm reading", which makes sense only next to the full
article, never shows without it. The renderer owns the length: a mail shows the blocks in full,
an output with less room (e.g. a future detail view in the web list) would show an excerpt of a
RichText block. `NotificationContext` tells `getBlocks()` where it is rendered: `channel` (the
target id, e.g. `MailTarget::ID`). It is constructed by the core only and may get more fields
later.

Buttons are links. Interactive quick actions with a server-side handler — *Accept*/*Decline* in
the web list or as push action buttons — are not link buttons and would be a separate API.

A notification whose content is the point of the message — a news article, a newsletter —
declares itself **standalone**:

```php
public static function standalone(): bool
{
    return true;
}
```

A standalone notification is never collected into one mail or push with others (where its
blocks would not appear): the delivery sends it as a message of its own, with its blocks. The
delay, the priority and the skipping rules (seen meanwhile, recipient online, channel switched
off) apply as for every other notification; see [Delivery](#delivery).

### Mail

A mail about one notification shows the sentence and the blocks inside the standard layout
(`@notification/views/mails/notification` and its plain-text twin), in the recipient's language.
Notifications that go out together share one mail ("3 new notifications"), each with its
sentence and a link, under the heading of its space where there is one; a group appears once
with the grouped sentence. Nothing in your module renders mail.

The activity summary mail mentions the number of unread notifications (the badge count,
`NotificationListService::unseenCount()`) with a link to the notification overview, when there
are any. It lists no notifications and is not sent because of them alone.

### Reference

The module-facing API, marked `@api` in the code — stable across minor versions:

- `BaseNotification`: `send()`, `revoke()`, `markSeen()`; the hooks `category()`, `priority()`,
  `listed()`, `standalone()`, `grouping()`, `getMessage()`, `getMessageParams()`,
  `getMailSubject()`, `getUrl()`, `getSubjectRecord()`, `getBlocks()`, `getSpace()`,
  `canReceive()`; the properties `$record`, `$recipient`, `$originator`,
  `$content`, `$contentContainer`, `$sourceRecord`, `$payload`, `$groupCount`
- `NotificationCategory`, `NotificationPriority`, `Grouping`, `NotificationBlock` (its
  factories), `NotificationContext` (its properties)
- `humhub\components\message\MessageParam`
- `BeforeDispatchEvent` and `NotificationManager::EVENT_BEFORE_DISPATCH`
- for channel providers: `BaseTarget`, `DeliveryBatch`, `MobileTargetProvider`

Everything else in the module — the manager's static methods, the services, the jobs — is
internal.

## Delivery

The web list and the live event are immediate (when the web channel is enabled for the
recipient, see [listing](#category-priority-listing)). For every other channel enabled for the recipient
(`BaseTarget::isEnabled()`), the dispatch job does not send but schedules: the
`DeliveryScheduler` writes a row per notification and channel into `notification_delivery`, with
a due time, and pushes a `DeliverJob` for the recipient and channel, delayed to that time.

- **Adaptive delay.** `n` is the number of *messages* (not notifications) the channel sent the
  recipient within `delayWindow`; the delay is `delays[min(n, count(delays) - 1)]`. With the
  defaults the first mail in a quiet hour goes out at once, the next one after five minutes, the
  one after that after fifteen, every further one after thirty. A `high` notification always goes
  at once, a `low` one waits at least `lowPriorityDelay`.
- **Riding along.** A new notification whose recipient already has a pending message on that
  channel, due at least 5 seconds from now and no later than the new one would be, takes over its
  due time; no job is pushed.
- **Collecting.** When a message goes out, the job takes every pending notification of the
  recipient and channel along — also those not due yet. A low-priority notification therefore
  usually leaves with the next message. Members of one group appear once, with the grouped
  sentence. The channel receives them as one `DeliveryBatch` — except the
  [standalone](#content-blocks) notifications, which go out one per batch, each with its own
  result (`sent`/`failed`). The messages of one run count once for the adaptive delay.
- **Skipping.** Right before sending, a row is skipped when the notification was seen in the
  meantime, when the channel is no longer active or enabled for it (category switch), when the
  recipient is no longer enabled, or when the notification no longer loads (e.g. its source is
  gone). A channel
  with `skipWhenOnline` (mail by default) skips the rows that are due while the user was active on
  the site within the last minute (`IsOnlineService::isRecentlyActive()`); rows not due yet stay
  pending.
- **Follow-ups and the sweep.** One job per recipient and channel runs at a time (a mutex). After
  each run, a job is pushed for the earliest pending row left. The hourly cron re-queues pending
  rows overdue by more than five minutes (a lost job) and deletes finished rows after 30 days
  (`DeliveryService::sweep()`).

A row ends as `sent`, `skipped` or `failed` (the channel threw; logged in the category
`notification`, no retry). A failing channel does not stop the others.

The delays need a queue that runs a job no earlier than its delay. With the `Instant` and `Sync`
drivers, or a driver the core does not know, every delay counts as 0: each notification goes out
at once, one message each. `components.notification.instantDelivery` (`true`/`false`, default
`null` = detect) overrides the detection. Due times are local wall-clock datetimes; across a DST
change a delay may be an hour shorter or longer.

A module never deals with any of this; it sends, the rest follows from the category, the
priority and the user's settings.

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
    public const ID = 'chat';

    public string $id = self::ID;

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

The target inherits the delay properties (`delays`, `delayWindow`, `lowPriorityDelay`,
`skipWhenOnline`, see [Delivery](#delivery)) and the per-user category switches; it appears on
the settings page as one more checkbox per category. Override `isActive(?User $user)` (calling the
parent) when the channel is not available to everyone, and `appliesTo(NotificationCategory $category)`
when it cannot carry some categories (every category by default; the web list only those with a
listed class) — those categories get no switch for the channel. `isEnabled($notificationClass,
$user)` is true when the target is active and the class's category is on for it. Push providers implement
`MobileTargetProvider` — `isActive()` and the same `deliver(DeliveryBatch)` — and register it in
the DI container under that interface.

The properties `$id` (`string`) and `$active` (`bool`) are typed; a subclass redeclares them
with the same types.

## Settings

Users switch every category other than `direct` on or off per channel — the web list included —
under *Account settings → Notifications*. The page lists the categories with a badge per
channel; a category expands to a checkbox per channel that applies to it, and the *new content*
category also holds the choice of spaces that send it. Profiles set all switches at once:
*Everything* (every category on every channel), *Recommended* (the administrator's defaults:
the user's own switches are removed, the spaces kept), *Important only* (the web list complete,
other channels only the categories that cannot be switched off) and *Custom*, selected whenever
the switches match none of the others. Every change is saved at once. Administrators set the
defaults under *Administration → Settings → Notifications* (the same page without profiles) and
can reset every user to them (*Reset all users*, shown to administrators who may also manage
users).

The keys, in the notification module's settings (global defaults) and user settings:
`<channel>.category.<category id>` (`mobile.category.social`, `web.category.followers`,
`email.category.example`). A user without an own value inherits the global default; without one
the category's default decides (`NotificationCategory::isEnabledByDefault()`). A category that
is not switchable is always on. `NotificationSettingsService` reads and writes them.

A module that needs to know whether a user wants a kind of notification on a channel asks the
target:

```php
Yii::$app->notification->getTarget(MailTarget::ID)->isEnabled(MailNotification::class, $user);
```

## API and live updates

- `GET /api/v2/notification` — the caller's own list, one entry per group, newest group first,
  cursor-paged (`cursor`, `limit`, `categories[]`, `seen`); `POST
  /api/v2/notification/mark-as-seen` — by `ids[]` (their whole groups) or all. Shapes in
  `notification\serializers\NotificationSerializer`.
- `GET`/`PATCH /api/v2/notification/settings` — the settings page's data
  (`NotificationSettingsService::toArray()`/`fromArray()`): `scope`, `channels` (`[{id, title}]`),
  `categories` (`[{id, title, description, icon, module, fixed, channels: {<channel>: bool}}]`,
  only the channels that apply to the category), `defaults` (the user's switches without own
  settings, `{<category>: {<channel>: bool}}`; `null` for the global scope) and `spaces`. `PATCH`
  is partial: `{categories: {<category>: {<channel>: bool|null}}, spaces: [ids]}` (`null`
  removes the switch, so the default applies again), `422` with errors under `categories`,
  `categories.<category>`, `categories.<category>.<channel>` or `spaces`. `?scope=global` for
  the administrator's defaults. `POST /api/v2/notification/settings/reset` resets the caller,
  `POST /api/v2/notification/settings/reset-all` every user (`ManageSettings` and
  `ManageUsers`). The pages `/notification/user` and
  `/notification/admin/defaults` are the `NotificationSettings` island
  (`notification\widgets\SettingsPage`).
- The live event `humhub\modules\notification\live\NewNotification` is sent for every
  notification stored as listed; the `NotificationMenu` island refreshes its count and list from it.

## Migrating from 1.19

The 1.19 API — `SocialActivity`, the builder `instance()->from()->about()->send()`, `html()`,
`viewName`, view files, notification category classes — is gone. The
[module migration guide](module-migrate-1.20.md) lists every replacement; the short version: a
notification class is constructed from its record, writes its sentence in `getMessage()`, is
sent with its static `send()`, and belongs to its module's category unless `category()` names
another one.
