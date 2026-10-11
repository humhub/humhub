# Activities

Activities record noteworthy things that happened in a content container — "Anna created a
post", "Bob joined the space", "Clara likes a comment". They appear in the activity box of the
dashboard, the space and the profile, and in the activity summary mail. An activity is bound to
its container and addressed to nobody in particular: whoever may see the container (and the
content) sees it. Compare with [notifications](concept-notifications.md), which are written for
specific recipients.

## The activity class

Place it under your module's `activities/` directory; every class there extending
`BaseActivity` is picked up (`Module::getActivityClasses()`). An activity about a content
extends `BaseContentActivity`, one about the container itself `BaseActivity`:

```php
namespace johndoe\example\activities;

use humhub\modules\activity\components\BaseContentActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use johndoe\example\models\Task;
use Yii;

/**
 * @extends BaseContentActivity<Task>
 */
final class TaskCreatedActivity extends BaseContentActivity implements ConfigurableActivityInterface
{
    protected string $contentActiveRecordClass = Task::class;

    public static function getTitle(): string
    {
        return Yii::t('ExampleModule.base', 'Tasks');
    }

    public static function getDescription(): string
    {
        return Yii::t('ExampleModule.base', 'Whenever a new task was created.');
    }

    protected function getMessage(array $params): string
    {
        return Yii::t('ExampleModule.base', '{displayName} created the task {contentTitle}.', $params);
    }
}
```

There are no view files. The same `getMessage()` renders the entry in the activity box
(`asWeb()`), the HTML summary mail (`asMailHtml()`) and its plain-text version (`asMailText()`);
only `$params` differs — on the web and in the HTML mail values are HTML-encoded and names are
bold, in plain text they are not. The built-in parameters:

| Parameter | Meaning |
|---|---|
| `displayName` | the user who caused the activity |
| `displayNames` | the users of a grouped activity: "Anna, Bob and 2 more" (empty when ungrouped) |
| `groupCount` | how many activities the entry stands for (1 when ungrouped) |
| `content` | type and preview of the content: *post "Release notes"* (`BaseContentActivity` only) |
| `contentTitle` | the preview alone (`BaseContentActivity` only) |
| `spaceName` | the name of the space (`BaseSpaceActivity` only) |

### Message parameters of your own

Declare them once in `getMessageParams()`; the core renders them for each output, exactly as for
[notifications](concept-notifications.md). A value is a plain string or a
`humhub\modules\notification\components\MessageParam`:

```php
use humhub\modules\notification\components\MessageParam;

protected function getMessageParams(): array
{
    return [
        'task' => MessageParam::emphasis($this->contentActiveRecord->title), // <strong>…</strong> / “…”
        'assignee' => MessageParam::user($this->contentActiveRecord->assignee), // <strong>…</strong> / as it is
        'parent' => MessageParam::content($this->contentActiveRecord->parent),  // task "Release…" / the same as plain text
        'reason' => MessageParam::text($this->reason, maxLength: 100),       // encoded / as it is
        'list' => $this->contentActiveRecord->list->name,                      // a plain string: like text()
    ];
}
```

| Value | Activity box and HTML mail | Plain text mail |
|---|---|---|
| plain string, `MessageParam::text()` | HTML-encoded | as it is |
| `MessageParam::emphasis()` | HTML-encoded in `<strong>` | in “quotes” |
| `MessageParam::user()` | the display name, HTML-encoded in `<strong>` | the display name |
| `MessageParam::content()` | type and preview of a content or comment, the preview as short HTML (in `<strong>` in the mail) | the same as plain text |
| a number | as it is (e.g. for an ICU `plural`) | as it is |

Your parameters are merged over the built-in ones, so a key like `content` replaces the built-in
value — `LikeActivity` names the liked comment that way, with `MessageParam::content()`. Its
preview, and one of your own, has the length of the output being rendered: `getPreviewLength()`
returns 60 for the activity box and 300 for mails (`$webContentLength` and `$mailContentLength`
of `BaseContentActivity`), as `NewCommentActivity` uses for the comment text. When you extend a class that has parameters of its own, merge
`parent::getMessageParams()`.

Up to 1.19, parameters were added by overriding `getMessageParamsWeb()`,
`getMessageParamsMailHtml()` and `getMessageParamsMailText()` (and names formatted with
`formatDisplayNames()`). These methods are **deprecated** since 1.20 and `@internal`: they keep
working — an override is still called and its result wins, also over `getMessageParams()` — but
new code uses `getMessageParams()` alone.

### The sentence without content

The administrator can hide content in the mails that leave the network — the *privacy mode* of
the [notification settings](../admin/notifications.md#privacy-of-e-mails-and-push). With *Hide
content* the summary mail shows each activity by `getRedactedMessage(array $params): ?string`
instead of `getMessage()`, without the Space name; with *Minimal* it only tells how many
activities there are. The activity box is unaffected.

Without the method, the core shows `getMessage()` itself when it names no content — rendered with
user names, numbers and each `MessageParam::content()` as its type alone, as for
[notifications](concept-notifications.md#privacy-mode) — and else "{displayName}: {title}" (see
below). Implement it when your sentence names a title, a Space or other text, or to fit the
grammar better:

```php
protected function getRedactedMessage(array $params): ?string
{
    return Yii::t('ExampleModule.base', '{displayName} created a task.', $params);
}
```

`$params` holds only values that carry no content: `displayName`, `displayNames`, `groupCount`,
`contentType` (the type of the activity's content, e.g. "post"; only for an activity about a
content) and those of your `getMessageParams()` that are a `MessageParam::user()` — names are
fine, everything else (`content`, `contentTitle`, titles, previews) never reaches the method.
Write a whole sentence, as for `getMessage()`, so translators can build it in their language;
pieces glued together do not translate. Only values passed through `$params` are recognized:
text written into `getMessage()` directly shows under *Hide content* as well. An activity that
still overrides the deprecated `getMessageParams*()` methods gets no automatic sentence.

The method is optional (since 1.20): when neither it nor `getMessage()` fits, the line is
"{displayName}: {title}", the title of a `ConfigurableActivityInterface` (e.g. "Anna Smith:
Tasks"), else "New activity". The core activities implement it where their sentence names more
than people and content types ("Anna Smith created a new post.", "Anna Smith joined a Space.").

### The rest of the class

The object is constructed from its `Activity` record and exposes `$record`, `$user`,
`$contentContainer`, `$createdAt` and `$groupCount`; `BaseContentActivity` adds `$content`,
`$contentActiveRecord` (checked against `$contentActiveRecordClass`) and `$contentAddon` (e.g. the
comment), `BaseSpaceActivity` adds `$space` and `inSpaceContext()`. `getUrl(bool $scheme = false)`
links the entry: the content addon, else the content, else the container — relative by default,
the summary mail asks for `getUrl(true)`.

What a module may use or override is marked `@api` — `send()`, `grouping()`, `getMessage()`,
`getMessageParams()`, `getRedactedMessage()`, `getUrl()`, the properties above, `ConfigurableActivityInterface`,
`BeforeDispatchEvent` and `ActivityManager::EVENT_BEFORE_DISPATCH`; everything marked `@internal`
(the grouping and rendering services, the deprecated parameter methods) may change without notice.

## Sending

```php
TaskCreatedActivity::send($task, $user);
```

The target is a content record, a content addon (a comment, a like — stored with its content)
or a container (`Space`, `User`). The user defaults to the logged-in one. `send()` writes
the record, groups it and sends the live event `activity\live\NewActivity`, from which the
`ActivityBox` island refreshes; it returns the activity, or `null` when a handler vetoed it. The
usual place is `afterSave()` of the record, once per state transition.

Before the record is written, `ActivityManager::EVENT_BEFORE_DISPATCH` is triggered with a
`humhub\modules\activity\events\BeforeDispatchEvent` (`class`, `target`, `user`); a handler
skips the activity with `$event->isValid = false`:

```php
Event::on(ActivityManager::class, ActivityManager::EVENT_BEFORE_DISPATCH, function (BeforeDispatchEvent $event) {
    if ($event->class === LikeActivity::class) {
        $event->isValid = false;
    }
});
```

Visibility and lifetime follow the content: an activity is shown only to who may see its
content, moves with it (`ActivityManager::afterContentChange()`), and is deleted with the
content, the container or the user.

## Grouping

Five people liking the same comment, ten files uploaded at once: a class declares which of its
activities belong together, and the box shows one entry. `grouping()` returns a
`humhub\modules\notification\components\Grouping`, the value object [notifications](concept-notifications.md#grouping)
are grouped with:

```php
use humhub\modules\notification\components\Grouping;

public static function grouping(): ?Grouping
{
    return Grouping::byContainer();
}
```

The core always adds the class and a time bucket (`withTimeBucket()`, 900 seconds by default);
a group forms from `withThreshold()` (2) matching activities. The factories `byContent()`
(`content_id`), `bySource()` (the content addon), `byContainer()` and `byClass()` name what must
be present and the same; the modifiers `andContent()`, `andSource()`, `andContainer()`,
`andOriginator()` (the user who caused it), `andContentType()`, `andContentVisibility()` and the
activity-only `andSourceParent()` (the content addon the source belongs to, e.g. the comment a
like is about) narrow it. `unseenOnly()` makes no sense for activities and fails with an
`InvalidConfigException`. Grouping happens when the activity is written. Branch on `$groupCount`
in `getMessage()` for the grouped sentence and use `displayNames`.

| Class | Grouping |
|---|---|
| `ContentCreatedActivity` | `Grouping::byClass()->andContainer()->andOriginator()->andContentType()->andContentVisibility()->withThreshold(4)` |
| `LikeActivity` | `Grouping::byContent()->andSourceParent()` — the likes of one content or one comment |
| `FollowActivity`, `MemberAddedActivity`, `MemberRemovedActivity` | `Grouping::byContainer()` |

### Deprecated

Kept working, no runtime warnings, but new code uses the API above:

- `ActivityManager::dispatch($class, $target, $user)` — use `MyActivity::send($target, $user)`.
- `getGroupingQuery()` — the query finding the siblings of an activity. An override is still
  used (and wins over `grouping()`), narrowed to a time bucket of `$groupingTimeBucketSeconds`
  and grouped from `$groupingThreshold` matches on; both properties are deprecated as well and
  only apply to such an override.
- `getMessageParamsWeb()`, `getMessageParamsMailHtml()`, `getMessageParamsMailText()` and
  `formatDisplayNames()` — use `getMessageParams()`, see above.
- The event data in `$event->data` (`class`, `target`, `user`) — read the properties of
  `BeforeDispatchEvent`.

## Summary mail settings

An activity implementing `ConfigurableActivityInterface` — static `getTitle()` and
`getDescription()` — appears as a checkbox under *E-Mail Summaries*, in the account settings and
in the administrator's defaults, so users can leave it out of their summary mail. Without the
interface it is always included.

The summary mail also mentions the number of unread notifications, with a link to the
notification overview; it is not sent for notifications alone. It follows the notification
privacy mode: *Hide content* shows the sentences without content
([above](#the-sentence-without-content)) and no Space names, *Minimal* only the number of new
activities with a link to the dashboard.

## API

`GET /api/v2/activity` (`containerId`, `cursor`, `limit`) returns the activities the caller may
see, newest entry first, one entry per group; the shape is in
`activity\serializers\ActivitySerializer`.
