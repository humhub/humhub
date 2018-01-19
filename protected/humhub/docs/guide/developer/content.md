Content
=======

Content record classes as for example `Post`, `Poll` and `Wiki` are subclasses of 
[[\humhub\modules\content\components\ContentContainerActiveRecord]].
 Instances ofand are related to a 
[[humhub\modules\content\models\Content]] record. 
A ContentContainerActiveRecord subclass provides all features of a basic 
Yii [ActiveRecords](http://www.yiiframework.com/doc-2.0/yii-db-activerecord.html) as validation and data access methods,
 please refer to the [Yii Guide](http://www.yiiframework.com/doc-2.0/guide-db-active-record.html) for more information
  about [ActiveRecords](http://www.yiiframework.com/doc-2.0/yii-db-activerecord.html).

While the ContentContainerActiveRecord class contains the actual content data as texts and content settings, the related Content instance is beside others used to check **Permissions**, the **ContentContainer** relation, content **visibility** and is furthermore connected to ContentAddons as Like and Comments.

Beside the basic ActiveRecord methods your ContentContainerActiveRecord class should at least implement the following functions

-  `getContentName()` - short name/type of content
-  `getContentDescription()` - returns a short description of the content instance used to preview the content for example in activities etc.

```php
class Example extends \humhub\modules\content\components\ContentContainerActiveRecord
{
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

    .....

}
```

Your content model should be **instantiated** as follows:

```php
// Instantiate my model assign a content container and visibility.
$model = new MyModel();
$model->content->container = $someSpace;
$model->content->container = Content::VISIBILITY_PRIVATE;
...
// Save model and content
$model->save();
```

Get the model instance from a given content instance:

```php
$model = $content->getPolymorphicRelation();
```

Calling [[\humhub\modules\content\components\ContentActiveRecord::find()]] will return a [[\humhub\modules\content\components\ActiveQueryContent]] with additional methods to select specific content:

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

### Content features

**Content visibility**

The content visibility can be checked by calling `isPublic()` and `isPrivate()`.

```php
$model->content->isPublic();

$model->content->isPrivate();

// Set visibility
$model->content->container = Content::VISIBILITY_PRIVATE;
```

**Pin content** 

The default space stream supports the pinning of content, which will load the pinned entries at the top of the
stream. Normally you won't need to call the pin/unpin methods by yourself, since this is part of the default stream
entry logic. In case your content is not part of the default stream, you may use these functions for your own module logic.

```php
$model->content->pin();

$model->content->unpin();

$model->content->isPinned();

$model->content->canPin();
```

**Archive content**

Archived content is by default excluded from the streams. As with the pin logic, you won't have to handle this by yourself.

```php
$model->content->archive();

$model->content->unarchive();

$model->content->isArchived();

$model->content->canArchive();

```

**Content Url**

By default the `Content::getUrl()` returns the permalink of the wallentry. In case the content is used outside of the default stream, this behaviour can be changed by implementing a `getUrl()` method in your ContentActiveRecord class.

```php
$permaLink = $model->content->getUrl();
```

### Check content permissions

By default a user can edit a content if one of the following conditions defined in `Content::canEdit()` are met:

- User is the owner of the content
- User is system administrator and the content module setting `adminCanEditAllContent` is set to true (default)
- The user is granted the space ManagePermission set by the model record class. Since v1.2.1 a ContentContainerActiveRecord can define an own `$managePermission` which will be described later.
- The user meets the additional condition, which is defined in the ContentContainerActiveRecords `canEdit()` function.

You can check the edit permission of a user by means of the `Content::canEdit()` function as

```php
// Check edit permission for current logged user
if($model->content->canEdit()) {...}

// Check edit permission for a given user
if($model->content->canEdit($someUserIdentity)) {...}

// Check other permission for the current logged user on the contents contentContainer
if($model->content->can(new MyCustomPermission()) {...}
```

Since HumHub v1.2.1 you can overwrite the default ManageContent permission as follows:

```php
class Example extends ContentContainerActiveRecord
{
    $managePermission = MyCustomManagePermission::class;
    .....
}
```

The default `Content::canView()` permission behaviour of content is handled as follows

- Guests can only access public content of visible spaces/users
- Other users can access all public content within the network
- System admins can access all content if the `adminCanViewAllContent` setting of the `content` modules is enabled (default)
- All space members can access private space content
- Non space members can only access public space content
- Only friend users can access prviate profile content of a user.

```php
if($model->content->canView()) {...}
```

>Info: For more information about permissions, please see the [Permission Section](module-permissions.md).

## ContentContainer

A [[humhub\modules\content\models\ContentContainer|ContentContainer]] in HumHub is the base concept for assigning content entries to a specific container instance (user or space).
Each [[humhub\modules\content\models\ContentContainer|ContentContainer]] is assigned with an unique guid, which is used in controllers to identify the context of its actions.

Currently there are two types of ContentContainer:
 
- [[humhub\modules\user\models\User|User]]
- [[humhub\modules\space\models\Space|Space]]

![Application Layers](images/contentContainerClassDiag.jpg)

> Note: It's not possible to create own ContentContainer classes, yet.

### ContentContainerController

The [[humhub\modules\content\components\ContentContainerController|ContentContainerController]] class is extended by controllers working in the context of a specific [[humhub\modules\content\models\ContentContainer|ContentContainer]].
A [[humhub\modules\content\components\ContentContainerController|ContentContainerController]] will automatically search for a **sguid** (Space) or **uguid** (User) request parameter in every request and will instantiate the corresponding [[humhub\modules\content\models\ContentContainer|ContentContainer]].

The [[humhub\modules\content\components\ContentContainerController|ContentContainerController]] provides common tasks like:

- Automatic container loading based on URL
- Access checks
- Layout selection based on container type (User or Space)
- Create URL's for the given ContentContainer

```php
class ExampleController extends \humhub\modules\content\components\ContentContainerController
{
    public function actionIndex()
    {
        if ($this->contentContainer instanceof Space) {
            //Space related stuff...
        }
    }
}
```

Urls pointing to a ContentContainer action should be created by using the `createUrl()` function of your ContentContainer instance. This will add the required sguid or uguid to your request.

```php
 // Direct ContentContainer call
 $space->createUrl('/module/controller/action');

 // Within a ContentContainerController:
 $this->contentContainer->createUrl('/module/controller/action');
```

> Note: Requests for a [[humhub\modules\content\components\ContentContainerController|ContentContainerController]] action without providing a sguid or uguid parameter will fail!

### ContentContainerActiveRecord

Each ContentContainer class is derived from [[\humhub\modules\content\components\ContentContainerActiveRecord]].
Beside others, this abstract class provides the following functionality:

- [Permission Management](dev-permissions.md) `getPermissionManager()`
- Profile-/Banner-image access `getProfileImage()`, `getProfileBannerImage()`
- Rendering the container stream `getWallOut()` (see [Permission Management](dev-stream.md))

Profile image example:

```php
//Get Profile Image Url
$profileImage = $space->getProfileImage();
if($profileImage->hasImage()) {
    $url = $profileImage->getUrl();
}
```

### ContentContainerModule

See the [Getting Started](modules-index.md) section

#### Content Streams

See the [Stream](stream.md) section

## Global content
(TBD)

## Content addons

TBD

- Always linked to particual Content, inherits access rules from it
- Examples: Like, File, Comment
- Can be nested (e.g. Container -> Content -> Addon (Comment) -> Addon (Like)
