Content
=======

Before you keep reading, make sure you are familiar with the following Yii concepts:
 
 - [Yii Models](https://www.yiiframework.com/doc/guide/2.0/en/structure-models)
 - [Yii ActiveRecords](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record)
 - [Yii Controller](https://www.yiiframework.com/doc/guide/2.0/en/structure-controllers)

## Content and ContentActiveRecords

[[humhub\modules\content\models\Content|Content]] entries are the base of HumHub content-types as `Posts`, `Polls` and `Wiki Pages`. `Content` entries reside in the
`content` table and are always related to a specific [[\humhub\modules\content\components\ContentActiveRecord|ContentActiveRecord]] by a polymorphic relation defined in the `object_model`
and `object_id` columns of the `content` table.

While the `ContentActiveRecord` holds the actual data as texts and content-type related settings,
the underlying `Content` class provides:

- **Permission** checks as `canEdit()`, `canView()`
- **ContentContainer** access
- **Visibility** settings `public` or `private`

Custom `ContentActiveRecord` classes can be implemented in case you need to embed a custom content-type to a [Stream](stream.md)
or in order to enable `Likes` and `Comments` on your content. 

The following example shows the basic usage of a `ContentActiveRecord`:

```php
// Post is a subclass of ContentActiveRecord and message is an attribute of the post table
$post = new Post($space, ['message' => 'This is an example post']);

// You can access the underlying content instance (which was automatically) created like this
$content = $post->content;

// You can access the related ContentContainerActiveRecord as follows
$space = $post->content->container;

// This will validate and save the post entry and the underlying content
$post->save();
```

In practice your model data is usually loaded into your model from a `post` request within your controller logic as in the following basic example:

```php
public function actionEdit($id = null) {
    $news = empty($id) ? new News() :  News::findeOne(['id' => $id]);
    
    if(!$news->content->canEdit()) {
        throw new HttpException(403);
    }
    
    if($news->load(Yii::$app->request->post() && $news->save()) {
        $this->view->saved();        
        return $this->render('view', ['news' => $news]);
    }
    
    return $this->render('edit', ['news' => $news]);
}
```

Just like other [ActiveRecords](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record) `ContentActiveRecord` classes should be put under the `models` namespace of your module.
Beside the basic `ActiveRecord` features as `validation` and `attributeLabels` your `ContentContainerActiveRecord` class should at least implement the following fields and methods:

- `moduleId` - id of the related module
- `getContentName()` - short name/type of content
- `getContentDescription()` - returns a short description of the content instance used to preview the content for example in activities etc.

```php
namespace mymodule\models;

use humhub\modules\content\components\ContentActiveRecord;

class MyModel extends ContentActiveRecord
{
    protected $moduleId = 'mymodule';
    
    protected $canMove = true;
    
    public static function tableName()
    {
        return 'example_content';
    }

    public function getContentName()
    {
        return Yii::t('ExampleModule.models_Example', "Example");
    }

    public function getContentDescription()
    {
        return $this->question;
    }

    public function rules()
    {
        //return validation rules
    }
}
```

### Use of ContentContainerActiveRecords

You can instantiate your `ContentContainerActiveRecord` as follows:

```php
// Only provide an array of attributes
$model = new MyModel(['some_field' => $field]);

// Instantiate my model by providing only content-container, the default visibility of the space will be used
$model = new MyModel($someSpace);

// Provide content-container and attribute options
$model = new MyModel($someSpace, ['some_field' => $field]);

// Instantiate my model by providing content-container and visibility
$model = new MyModel($someSpace, Content::VISIBILITY_PRIVATE);

// Provide content-container, visibility and options array
$model = new MyModel($someSpace, Content::VISIBILITY_PRIVATE, ['some_field' => $field]);

// Setting of container and visibility and fields manually
$model = new MyModel();
$model->content->container = $someSpace;
$model->content->visibility = Content::VISIBILITY_PRIVATE;
$model->some_field = $field;

// Save model and content
$model->save();
```

When given a `Content` instance, you can access the related model as follow:

```php
$model = $content->getModel();
```

> Note: You won't have to worry about instantiating or saving the underlying content record, since this is handled within
the ContentContainerActiveRecord class automatically.

### Use of ActiveQueryContent

The `Content` class furthermore provides some extended [ActiveQuery](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record#querying-data) capabilities.
Calling [[\humhub\modules\content\components\ContentActiveRecord::find()|ContentActiveRecord::find()]] will return a [[\humhub\modules\content\components\ActiveQueryContent]] instance with additional methods to filter specific content entries:

```php
// Returns all MyModels related to the given $space
$models = MyModel::find()->contentContainer($space)->all();

// Returns all MyModels related to the given $space and readable by the current user
$models = MyModel::find()->contentContainer($space)->readable()->all();

// Loads all MyModels of the current users member spaces
$models = MyModel::find()->userRelated([ActiveQueryContent::USER_RELATED_SCOPE_SPACES])->all();

// Loads all readable MyModels of the current users spaces and also followed spaces
$models = MyModel::find()->userRelated([
    ActiveQueryContent::USER_RELATED_SCOPE_SPACES,
    ActiveQueryContent::USER_RELATED_SCOPE_FOLLOWED_SPACES
])->readable()->all();
```

There are the following user related scopes available:

- `USER_RELATED_SCOPE_OWN` Content created by the given user itself (`content.created_by`)
- `USER_RELATED_SCOPE_SPACES` Content related to the users member spaces
- `USER_RELATED_SCOPE_FOLLOWED_SPACES` = Content related to the users followed spaces
- `USER_RELATED_SCOPE_FOLLOWED_USERS` = Content related to the users followed user profiles
- `USER_RELATED_SCOPE_OWN_PROFILE` = Content related to the users own profile

### Move Content (since v1.3)

In case your content should be movable to other spaces you'll have to enable the [[\humhub\modules\content\components\ContentActiveRecord::canMove|ContentActiveRecord::canMove]] flag.
For complex content-types you may want to overwrite the [[\humhub\modules\content\components\ContentActiveRecord::afterMove()|ContentActiveRecord::afterMove()]] function.
This is required for example if your content is related to other sub content entries.

```php
public function afterMove($container = null)
{
    foreach($this->subcontent as $subcontent)
    {
        $subcontent->move($container);
    }
}
```

### Silent Content Creation

By default, the creation of a content entry triggers an activity and notifications for space subscribers.
This behaviour can be deactivated by setting the [[\humhub\modules\content\components\ContentActiveRecord::silentContentCreation|ContentActiveRecord::silentContentCreation]]
to `true`. This setting can for example be used for sub content-types which are not of high interest.

### Content Visibility

The content visibility specifies who is be allowed to view the content. When instantiating a new `ContentActiveRecord` without [a predefined visibility](#instantiating-a-contentcontaineractiverecord)
the underlying content will adapt the default content visibility of its `ContentContainerActiveRecord`.

- `Private` content will only be visible for `Space Members` (Space) and `Friend Users` (Profile).
- `Public` content will be visible for everyone including `guests` if the guest mode is active.

The content visibility can be checked by calling `isPublic()` and `isPrivate()` on the `Content` instance.

```php
$model->content->isPublic();

$model->content->isPrivate();

// Set visibility
$model->content->container = Content::VISIBILITY_PRIVATE;
$model->save();
```

The visibility of a content for a user can be validated by the `Content::canView()` method and behaves by default as follows:

- Guests can only access public content of visible spaces/users
- Other users can access all public content within the network
- System admins can access all content if the `adminCanViewAllContent` setting of the `content` modules is enabled (default)
- All space members can read private space content
- Non space members can only access public space content
- Only friend users can access private profile content of a user.

```php
if(!$model->content->canView()) {
 throw new HttpException(403);
}
```

> Note: Many content-types provide a private/public flag within the create/edit form.

> Info: Private spaces can not create public content.

### Content Write Permission

By default users can edit a content if one of the following conditions defined in `Content::canEdit()` are met:

- User is the owner of the content
- User is system administrator and the content module setting `adminCanEditAllContent` is set to true (default)
- The user is granted the space ManagePermission set by the model record class. Since v1.2.1 a ContentContainerActiveRecord can define an own `$managePermission` which will be described later.
- The user meets the additional condition, which is defined in the ContentContainerActiveRecords `canEdit()` function.

You can check the edit permission of a user by means of the `Content::canEdit()` function as

```php
// Check edit permission for current logged user
if($model->content->canEdit()) {
 //...
}

// Check edit permission for a given user
if($model->content->canEdit($someUserIdentity)) {
  //...
}

// Check other permission for the current logged user on the contents contentContainer
if($model->content->can(new MyCustomPermission()) {
  //...
}
```

You can overwrite the default ManageContent permission as follows:

```php
class Example extends ContentContainerActiveRecord
{
    $managePermission = MyCustomManagePermission::class;
    
    // ...
}
```

> Info: For more information about permissions, please see the [Permission Section](module-permissions.md).

### Other content features

#### Content Url

By default the url returned by `Content::getUrl()` links to the space or profile stream with active `contentId` filter.
If you want to change this behaviour and for example rather link to a content-type specific detail view of your content, you can
implement a `ContentActiveRecord::getUrl()` method.

```php
public function getUrl()
{
    return $this->content->container->createUrl('/mymodule/detail', ['id' => $this->id]);
}
```

Retrieve the content url:

```php
$url = $model->content->getUrl();
```

#### Pin content

The default space stream supports the pinning of content, which will load the pinned entries at the top of the
stream. Normally you won't need to call the pin/unpin methods by yourself, since this is part of the default stream
entry logic. In case your content is not part of the default stream, you may use these functions for your own module logic.

```php
$model->content->pin();

$model->content->unpin();

$model->content->isPinned();

$model->content->canPin();
```

#### Archive content

Archived content is by default excluded from the streams. As with the pin logic, you won't have to handle this by yourself.

```php
$model->content->archive();

$model->content->unarchive();

$model->content->isArchived();

$model->content->canArchive();

```

## ContentContainer

[[humhub\modules\content\models\ContentContainer|ContentContainers]] in HumHub are used to assign `Content` entries to a specific
container like a `User Account` or `Space`. Similar to `Content` and `ContentActiveRecords`, `ContentContainer` entries can be seen as an abstraction
of `ContentContainerActiveRecords`. Each `Content` entry (except global content) is related to one `ContentContainer` which again
is related to one `ContentContainerActiveRecord` (Space or User). In practice you'll usually directly work with `ContentActiveRecords` instances.

Each container is assigned with an unique `guid`, which beside others is used in controllers to identify the context of its actions.

The `ContentContainerActiveRecord` can be accessed as follows: 

```php
$news = News::findOne(['id' => $id]);
$container = $news->content->container; 
```

There are two types of `ContentContainerActiveRecords`:
 
- [[humhub\modules\user\models\User|User]]
- [[humhub\modules\space\models\Space|Space]]

![Application Layers](images/contentContainerClassDiag.jpg)

> Note: HumHub does not support custom container types.

## ContentContainerController

When working with `Content` or other `ContentContainer` related data, your controller should extend the [[humhub\modules\content\components\ContentContainerController|ContentContainerController]] class.
A `ContentContainerController` will automatically search and instantiate a `ContentContainerActiveRecord` related to a `cguid` request parameter and provide additional features as:

- Additional **access checks**
- Default **layout selection** based on container type (User or Space)
- Create **container URL's** for the given container

By default a `ContentContainerController` will block requests without a given `cguid` request parameter. If you need to implement a controller which
should be able to handle container related as well as global requests you'll have to set the `ContentContainerController::requireContainer` field to `false`.

In your controller logic you can access the `ContentContainerActiveRecord` within your controller by means of `$this->contentContainer`.

You can even restrict the allowed `ContentContainerActiveRecord` types by setting the `ContentContainerController::validContentContainerClasses` array. This can be
useful for example if your controller should only handle space related requests.

```php
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Space;

class ExampleController extends ContentContainerController
{

    public $validContentContainerClasses = [Space::class];
    
    //...
}
```

Urls pointing to a `ContentContainerController` action should be created by using the `ContentContainerActiveRecord::createUrl()` function. 
This will add the required cguid parameter to your request.

```php
 // Direct ContentContainer call
 $space->createUrl('/module/controller/action');
 
 // Or by means of a ContentActiveRecord
 $model->content->createUrl('/module/controller/action');

 // Within a ContentContainerController:
 $this->contentContainer->createUrl('/module/controller/action');
```

## ContentContainerModule

See the [Use of ContentContainerModule](modules-base-class.md#use-of-contentcontainermodule) section.

## Content addons

TBD

- Always linked to particual Content, inherits access rules from it
- Examples: Like, File, Comment
- Can be nested (e.g. Container -> Content -> Addon (Comment) -> Addon (Like)

## Content Streams

See the [Stream](stream.md) section
