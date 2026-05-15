# Content Containers

A *content container* is the owning context for content entries — currently a `Space` or a `User`. Each container has a unique `guid` used to identify the context in URLs and controllers. Content entries (posts, polls, wiki pages, …) belong to exactly one container, or are *global* (no container).

For the broader content concept — `ContentActiveRecord`, visibility, stream channels — see [concept-content.md](concept-content.md).

## Container types

| Class                                          | Purpose                              |
|------------------------------------------------|--------------------------------------|
| `humhub\modules\space\models\Space`            | Space (group container)              |
| `humhub\modules\user\models\User`              | User profile                         |

Both extend `humhub\modules\content\components\ContentContainerActiveRecord`. You cannot add new container types at the moment.

## `ContentContainerActiveRecord`

Common methods provided by every container:

- `getPermissionManager()` — see [permissions](concept-permissions.md)
- `getProfileImage()`, `getProfileBannerImage()` — image access
- `getWallOut()` — render the container's stream (see [streams](concept-stream.md))
- `createUrl()` — produce a URL that targets this container (adds `sguid` / `uguid`)

```php
$profileImage = $space->getProfileImage();
if ($profileImage->hasImage()) {
    $url = $profileImage->getUrl();
}
```

## `ContentContainerController`

Extend `humhub\modules\content\components\ContentContainerController` for controllers that always run in the context of a container. The controller:

- looks for `sguid` (Space) or `uguid` (User) in the request, instantiates the matching container, and exposes it via `$this->contentContainer`
- enforces access checks against that container
- selects the layout based on container type
- exposes `$this->contentContainer->createUrl()` for outgoing URLs

```php
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Space;

class ExampleController extends ContentContainerController
{
    public function actionIndex()
    {
        if ($this->contentContainer instanceof Space) {
            // space-specific handling
        }
    }
}
```

Build container-bound URLs via `createUrl()`:

```php
// from any caller
$space->createUrl('/example/foo/index');

// inside a ContentContainerController
$this->contentContainer->createUrl('/example/foo/index');
```

A request to a `ContentContainerController` action without `sguid` or `uguid` will fail.

## `ContentContainerModule`

A module that should be installable on a Space or User level extends `humhub\modules\content\components\ContentContainerModule` instead of `humhub\components\Module`. See [Module Class → ContentContainerModule](module-base-class.md#contentcontainermodule) for the full method reference.

The two essential overrides:

```php
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

class Module extends ContentContainerModule
{
    public function getContentContainerTypes(): array
    {
        return [Space::class, User::class];
    }

    public function disableContentContainer(ContentContainerActiveRecord $container)
    {
        foreach (MyContent::find()->contentContainer($container)->all() as $entry) {
            $entry->delete();
        }
        parent::disableContentContainer($container);
    }
}
```

`disableContentContainer()` is the place to clean up data when an admin disables your module on a specific container. The base class clears container-scoped settings; your override must delete your own content rows.

## Querying within a container

`ContentActiveRecord::find()` returns an `ActiveQueryContent`, which extends Yii's query with container-aware helpers:

```php
MyContent::find()
    ->contentContainer($container)   // restrict to one container
    ->readable($user)                // filter by what $user is allowed to see
    ->all();
```
