# Content Handling

The Content handling in HumHub is seperated into three tiers.

## ContentContainer

ContentContainers are the top level in the content hierarchy. Each piece of content must be assigned to a container.

Container Classes:
- User
- Space

*Note:* It's not possible to create own ContentContainer classes, yet.



### Controller

If the module working with Content you should use the [[humhub\modules\content\components\ContentContainerController]] as base.

It provides common tasks like:
- Automatic container loading based on URL
- Access checks
- Layout selection based container type (User or Space)

Example:

```php
class ExampleController extends \humhub\modules\content\components\ContentContainerController
{


}

```

### ActiveRecord

Each ContentContainer class is derived from [[\humhub\modules\content\components\ContentContainerActiveRecord]] 

TBD (URL, AccessChecking, ProfileImage)


### Module

If the module should be shown in the content containers module section, the module class must derived from [[humhub\modules\content\components\ContentContainerModule]].
 
You need at least to overwrite the method [[humhub\modules\content\components\ContentContainerModule::getContentContainerTypes]] which returns the valid content container classes.

```php
class Module extends humhub\modules\content\components\ContentContainerModule
{

    public function getContentContainerTypes()
    {
        return [Space::className()];
    }
}
```

If you're working with content or other persistent data, make also sure to delete it when the module is disabled on a content container. Do this by overwriting the method [[humhub\modules\content\components\ContentContainerModule::disableContentContainer]].

e.g.

```php
 TBD
```

See [[humhub\modules\content\components\ContentContainerModule]] class for a full list of  options.

## Content

TBD


### ActiveRecord

Each Content ActiveRecord (derived from [[\humhub\modules\content\components\ContentActiveRecord]]) is automatically linked to a [[humhub\modules\content\models\Content]] record via the *content* Attribute. 

This Content record holds all neccessary informations and provides common methods:

- ContentContainer which the Content belongs to
- Meta Informations (created_at, created_by, updated_at, updated_by)
- Wall Assignments / Methods
- Archiving / Sticking
- And more...

If you're implementing an ActiveRecord based on [[humhub\modules\content\components\ContentContainerActiveRecord]] you need to implement following abstract methods:

- getContentTitle() - Returns the displayed name of the Content (e.g. Post or Poll)
- getContentPreview($maxLength) - Returns a preview of the Content - which is used in Notifications for example.
- getWallOut() - Returns a Widget Containing the Output in Walls or Search Results

Example:

```php


```


#### Querying Content

If you're calling find() on a [[\humhub\modules\content\components\ContentActiveRecord]] instance you'll get an special [[\humhub\modules\content\components\ActiveQueryContent]] which provides additional method to select content.

- contentContainer($container) - Find content only inside a given container
- readable($user) - Return only user readable content
- ...

#### Permissions

TBD (Read Permissions not enhanceable)



### Controller

TBD

## ContentAddon

TBD

- Always linked to particual Content, inherits access rules from it
- Examples: Like, File, Comment
- Can be nested (e.g. Container -> Content -> Addon (Comment) -> Addon (Like)

### ActiveRecord

TBD

Provides access to the related content via *content *Attribute

### Controller

TBD