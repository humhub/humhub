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
`humhub\components\message\MessageParam`:

```php
use humhub\components\message\MessageParam;

protected function getMessageParams(): array
{
    return [
        'task' => MessageParam::emphasis($this->contentActiveRecord->title), // <strong>…</strong> / “…”
        'assignee' => MessageParam::user($this->contentActiveRecord->assignee), // <strong>…</strong> / as it is
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
| a number | as it is (e.g. for an ICU `plural`) | as it is |

Your parameters are merged over the built-in ones, so a key like `content` replaces the built-in
value — `LikeActivity` names the liked comment that way. For a preview of the length of the
output being rendered, `BaseContentActivity::getPreviewLength()` returns `$webContentLength` (60)
for the activity box and `$mailContentLength` (300) for mails, as `NewCommentActivity` does for
the comment text. When you extend a class that has parameters of its own, merge
`parent::getMessageParams()`.

Up to 1.19, parameters were added by overriding `getMessageParamsWeb()`,
`getMessageParamsMailHtml()` and `getMessageParamsMailText()` (and names formatted with
`formatDisplayNames()`). These methods are **deprecated** since 1.20 and `@internal`: they keep
working — an override is still called and its result wins, also over `getMessageParams()` — but
new code uses `getMessageParams()` alone.

### The rest of the class

The object is constructed from its `Activity` record and exposes `$record`, `$user`,
`$contentContainer`, `$createdAt` and `$groupCount`; `BaseContentActivity` adds `$content`,
`$contentActiveRecord` (checked against `$contentActiveRecordClass`) and `$contentAddon` (e.g. the
comment), `BaseSpaceActivity` adds `$space` and `inSpaceContext()`. `getUrl(bool $scheme = false)`
links the entry: the content addon, else the content, else the container — relative by default,
the summary mail asks for `getUrl(true)`.

What a module may use or override is marked `@api` — `send()`, `grouping()`, `getMessage()`,
`getMessageParams()`, `getUrl()`, the properties above, `ConfigurableActivityInterface`,
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
`humhub\components\grouping\Grouping`, the value object [notifications](concept-notifications.md#grouping)
are grouped with:

```php
use humhub\components\grouping\Grouping;

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
notification overview; it is not sent for notifications alone.

## API

`GET /api/v2/activity` (`containerId`, `cursor`, `limit`) returns the activities the caller may
see, newest entry first, one entry per group; the shape is in
`activity\serializers\ActivitySerializer`.
