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
only `$params` differs — on the web and in the HTML mail the names are bold and encoded, in plain
text they are not:

| Parameter | Meaning |
|---|---|
| `displayName` | the user who caused the activity |
| `displayNames` | the users of a grouped activity: "Anna, Bob and 2 more" (empty when ungrouped) |
| `groupCount` | how many activities the entry stands for (1 when ungrouped) |
| `content` | type and preview of the content: *post "Release notes"* (`BaseContentActivity` only) |
| `contentTitle` | the preview alone (`BaseContentActivity` only) |

Add parameters of your own by overriding `getMessageParamsWeb()`, `getMessageParamsMailHtml()`
and `getMessageParamsMailText()` with `array_merge(parent::…(), [...])`, as
`NewCommentActivity` does for the comment text. The object is constructed from its `Activity`
record and exposes `$record`, `$user`, `$contentContainer`, `$createdAt` and `$groupCount`;
`BaseContentActivity` adds `$content`, `$contentActiveRecord` (checked against
`$contentActiveRecordClass`) and `$contentAddon` (e.g. the comment). `getUrl()` links the entry:
the content addon, else the content, else the container.

## Dispatching

```php
use humhub\modules\activity\services\ActivityManager;

ActivityManager::dispatch(TaskCreatedActivity::class, $task, $user);
```

The target is a content record, a content addon (a comment, a like — stored with its content)
or a container (`Space`, `User`). The user defaults to the logged-in one. `dispatch()` writes
the record, groups it and sends the live event `activity\live\NewActivity`, from which the
`ActivityBox` island refreshes; it returns the activity, or `null` when a handler of
`ActivityManager::EVENT_BEFORE_DISPATCH` set `$event->isValid = false`. The usual place is
`afterSave()` of the record, once per state transition.

Visibility and lifetime follow the content: an activity is shown only to who may see its
content, moves with it (`ActivityManager::afterContentChange()`), and is deleted with the
content, the container or the user.

## Grouping

Five people liking the same comment, ten files uploaded at once: return the query that finds
the siblings of a new activity, and the box shows one entry.

```php
public function getGroupingQuery(): ?ActiveQueryActivity
{
    return Activity::find()
        ->andWhere(['activity.class' => self::class])
        ->andWhere(['activity.contentcontainer_id' => $this->contentContainer->id]);
}
```

The core narrows it to a time bucket of `$groupingTimeBucketSeconds` (900) and groups when at
least `$groupingThreshold` (2) activities match — both properties of the class. Grouping happens
when the activity is written. Branch on `$groupCount` in `getMessage()` for the grouped sentence
and use `displayNames`.

## Summary mail settings

An activity implementing `ConfigurableActivityInterface` — static `getTitle()` and
`getDescription()` — appears as a checkbox under *E-Mail Summaries*, in the account settings and
in the administrator's defaults, so users can leave it out of their summary mail. Without the
interface it is always included.

## API

`GET /api/v2/activity` (`containerId`, `cursor`, `limit`) returns the activities the caller may
see, newest entry first, one entry per group; the shape is in
`activity\serializers\ActivitySerializer`.
