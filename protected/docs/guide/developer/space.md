Space
=====

When your module should also appear in space module section you need to add the 
SpaceModuleBehavior to your Module Class.

```php

    class SomeModule extends HWebModule
    {

        public function behaviors()
        {

            return array(
                'SpaceModuleBehavior' => array(
                    'class' => 'application.modules_core.space.behaviors.SpaceModuleBehavior',
                ),
            );
        }

        //...

    }

```

See SpaceModuleBehavior Class for further details.

## Example: Add item to space navigation

Catch Space Navigation Init Event in your modules autostart.php.

```autostart.php

    Yii::app()->moduleManager->register(array(
        //...

        'events' => array(
            array('class' => 'SpaceMenuWidget', 'event' => 'onInit', 'callback' => array('ExampleModule', 'onSpaceMenuInit')),
        )

    ));

```

Define callback in your module to add item.

```php
    /**
     * On build of a Space Navigation, check if this module is enabled.
     * When enabled add a menu item
     * 
     * @param type $event
     */
    public static function onSpaceMenuInit($event) {

        $space = Yii::app()->getController()->getSpace();
        
        // Is Module enabled on this workspace?
        if ($space->isModuleEnabled('example')) {

            $event->sender->addItem(array(
                'label' => 'Some space navigation entry',
                'url' => '#',
                'icon' => 'icon',
                'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'example'),
            ));

        }
    }
```


## Access space by by module controller

By adding the SpaceControllerBehavior you are able to access current space in your controllers.
Make sure you always pass the current space guid (sguid) in your urls. 

When using the method createContainerUrl (provided by SpaceControllerBehavior or UserControllerBehavior) the
current space or user guid is automatically added to urls.

```php

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors()
    {
        return array(
            'SpaceControllerBehavior' => array(
                'class' => 'application.modules_core.space.behaviors.SpaceControllerBehavior',
            ),
        );
    }

    public function actionTest() {
        $currentSpace = $this->getSpace();
        
        $this->redirect($this->createContainerUrl('test2'));
        
    }

    public function actionTest2() {
        $currentSpace = $this->getSpace();
    }

```
