Space
=====

Space Modules can be enabled/disabled per space.


You need to the enable the flag **isSpaceModule** in the register module array.

    Yii::app()->moduleManager->registerModule(array(
        'id' => 'example',
        'title' => 'Example Space Module',
        'description' => 'A space example module',
        'isSpaceModule' => true
    ));

Then the module is available under the **Space -> Modules** Section.

Before manipulating e.g. the SpaceMenu you need to check that the module is enabled in the space.

You can check this by:

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

At this release it´s not possible to create on space types. 
This feature will be available in a future release.





