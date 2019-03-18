Content
=======

Before you keep reading, make sure you are familiar with the following Yii concepts:
 
 - [Yii Models](https://www.yiiframework.com/doc/guide/2.0/en/structure-models)
 - [Yii ActiveRecords](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record)
 - [Yii Controller](https://www.yiiframework.com/doc/guide/2.0/en/structure-controllers)
 
The content concept is a fundamental part of HumHub and allows the implementation of different types of content. 
The core content module is responsible for managing the visibility and other content features which are described in the following section.

## ContentContainer

As the name suggests, a ContentContainer in HumHub can be connected with content instances. HumHub supports two different 
types of ContentContainer:

 - User
 - Space

Content without a ContentContainer relation are considered global.

## Content and ContentActiveRecord 

The [[\humhub\modules\content\components\ContentActiveRecord|ContentActiveRecord]] class serves as the base class for
every content type as for example Polls, Posts or Wiki pages. While the ContentActiveRecord implementation
describes the specific behavior of a content type, all ContentActiveRecord instances are related 
to a [[humhub\modules\content\models\Content|Content]] record which holds general content data as:

 - visibility
 - originator
 - creation date
 - updater
 - update date
 - ContentContainer relation

The following section describes common configuration possibilities of ContentActiveRecord subclasses.

`ContentActiveRecord::moduleId` should always be set with the related module id of your content record.

`ContentActiveRecord::autoFollow` flag is used to determine whether or not the content originator should automatically follow
this content after creation. In case the originator should not receive notifications for e.g. likes or comments by default, this
flag should be set to false.

`ContentActiveRecord::streamChannel` specifies the main context in which this type of content is used. 
Activity content entries for example make use of an own stream channel and therefore are not included in the `default` wall stream channel. 
In case you just want to exclude your content from the wall stream, set the stream channel to `null`. 
This setting can also be changed by model updates in order to exclude the content only if some conditions are met.
As with activities, this field can also be used to create own custom streams.

`ContentActiveRecord::silentContentCreation` can be set to `false` if you want to prevent the creation of `ContentCreated`
notifications and activities for this type of content. Note, those activity records are only created when inserting
the content record. Changing this setting afterwards won't have any effect.

### Custom ContentActiveRecord

Just like other [ActiveRecords](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record) `ContentActiveRecord` 
classes should be put under the `models` namespace of your module. Beside the basic `ActiveRecord` features as `validation` and `attributeLabels` 
your `ContentActiveRecord` class should at least implement the following fields and methods:

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

### ContentActiveRecord usage

A ContentActiveRecord constructor can be called as follows:

Provide a content container and attribute config. In this case the default content visibility of the related container
will be used. This is the most common way of initializing a new ContentActiveRecord.

```php
new Post($space, ['message' => 'Some message'])
```

The following example provides an additional visibility setting in order to force a content visibility:

```php
new Post($space, Content::VISIBILITY_PRIVATE ['message' => 'Some message'])
```

A global content entry can be created as follows:

```php
new Post(['message' => 'Some message'])
```

You can instantiate your `ContentActiveRecord` as follows:

```php
// Setting of container and visibility and fields manually
$model = MyModel::findOne(['id' => $id]);
$space = $model->content->container;
$model->content->visibility = Content::VISIBILITY_PRIVATE;
$model->some_field = $field;
$model->save();
```

When given a `Content` instance, you can access the related model as follow:

```php
$model = $content->getModel();
```

> Note: You won't have to worry about instantiating or saving the underlying content record, since this is handled within
the ContentActiveRecord class automatically.

## Content visibility

The content visibility specifies who will be allowed to view a content instance. 
When instantiating a new `ContentActiveRecord` without [a predefined visibility](#contentactiverecord-usage)
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

## Content edit permission

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

> Note: Many content-types provide a private/public flag within the create/edit form.

> Info: Private spaces can not create public content.

## Content queries

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

- _USER_RELATED_SCOPE_OWN_: Content created by the given user itself (`content.created_by`)
- _USER_RELATED_SCOPE_SPACES_: Content related to the users member spaces
- _USER_RELATED_SCOPE_FOLLOWED_SPACES_: Content related to the users followed spaces
- _USER_RELATED_SCOPE_FOLLOWED_USERS_: Content related to the users followed user profiles
- _USER_RELATED_SCOPE_OWN_PROFILE_: Content related to the users own profile

## Move Content

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

## Other content features

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

## ContentContainerController

When working with Content or other ContentContainer related data, your controller should extend the
 [[humhub\modules\content\components\ContentContainerController|ContentContainerController]] class.
This controller will automatically search and instantiate a container instance related to the 
`cguid` request parameter and provide additional features as:

- Additional **access checks**
- Default **layout selection** based on container type (User or Space)
- Create **container URL's** for the given container

By default a ContentContainerController will block requests without a given `cguid` request parameter. 
If you need to implement a controller which should be able to handle container related as well as global 
requests you'll have to set the `ContentContainerController::requireContainer` field to `false`.

In your controller logic you can access the related cotnainer by means of `$this->contentContainer`.

You can even restrict the allowed container types by setting the `ContentContainerController::validContentContainerClasses` array. 
This can be useful if your controller should only handle space or user related requests.

```php
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Space;

class ExampleController extends ContentContainerController
{

    public $validContentContainerClasses = [Space::class];
    
    //...
}
```

Urls pointing to a container action should be created by using the `ContentContainerActiveRecord::createUrl()` function. 
This will add the required cguid parameter to your request.

```php
 // Direct ContentContainer call
 $space->createUrl('/module/controller/action');
 
 // Or by means of a ContentActiveRecord
 $model->content->createUrl('/module/controller/action');

 // Within a ContentContainerController:
 $this->contentContainer->createUrl('/module/controller/action');
```

Another way of creating container urls is the following:

```php
$url = \yii\helpers\Url::to(['/some/route', 'container' => $space);
```

## Content addons

Content addons can be used to extend the content concept with further features. Examples of content addons are

- [[humhub\modules\like\models\Like|Like]]
- [[humhub\modules\comment\models\Comment|Comment]]

## ContentContainerModule

See the [Use of ContentContainerModule](modules-base-class.md#use-of-contentcontainermodule) section.

## Content Streams

See the [Stream](stream.md) section
