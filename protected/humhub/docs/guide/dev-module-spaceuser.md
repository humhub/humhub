Space/User Modules
==================

TBD



## Enabled/Disable per Space/User


Tasks:
- Inherit your modules base class from [[\humhub\modules\content\components\ContentContainerModule]] 
- Define valid container types (e.g. Space or/and User)

Example

```php

// ...
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
// ...

class Module extends \humhub\modules\content\components\ContentContainerModule
{

	// ...
    /**
     * @inheritdoc
     */
    public function getContentContainerTypes()
    {
        return [
            Space::className(),
            User::className(),
        ];
    }


	// ...

}

```