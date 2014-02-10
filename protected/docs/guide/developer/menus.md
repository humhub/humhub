Menus
=====

You can modify each menu/navigation by intercepting menu events.

## Events of MenuWidget

* onInit    - at initialization of a menu
* onRun     - before the menu is rendered

## Menus

* TopMenuWidget - Top Navigation
* ProfileMenuWidget - User Profile Menu (User Module)
* AccountMenuWidget - User Account Menu (User Module)
* SpaceMenuWidget - Space Menu (Space Module)
* SpaceAdminMenuWidget - Space Admin Menu (Space Module)
* AdminMenuWidget - Admininistration Menu (Admin Module)

## Example

``autostart.php:``

    Yii::app()->interceptor->attachEventHandler('TopMenuWidget', 'onInit', array('ExampleModule', 'onTopMenuInit'));

''ExampleModule.php:''

    /**
     * On build of the TopMenu, check if module is enabled
     * When enabled add a menu item
     * 
     * @param type $event
     */
    public static function onTopMenuInit($event) {

        // Is Module enabled?
        if (Yii::app()->moduleManager->isEnabled('myModule')) {

            // Add Item to Menu 
            $event->sender->addItem(array(
                'label' => 'My new navigation item',
                'url' => Yii::app()->createUrl('/mymodule/url', array()),
                'icon' => 'time',
                'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'mymodule'),
            ));
        }
    }