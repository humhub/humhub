autostart.php
=============


Each module definition is stored within a file ``autostart.php`` inside the module root folder.
This file is required by each module.

``Note:`` Contents of autostart.php are cached in file /protected/runtime/cache_autostart.php - delete this file or flush caches after modifing this file!

Example of a autostart.php File
-------------------------------

    <?php
    Yii::app()->moduleManager->register(array(

        // Unique ID of the module, same as the module folder
        'id' => 'example',

        // Module Base Class (http://www.yiiframework.com/doc/guide/1.1/en/basics.module)
        'class' => 'application.modules.example.ExampleModule',

        // Title of the Module inside Administration Interface
        'title' => Yii::t('ExampleModule.base', 'Example'),

        // Short Description
        'description' => Yii::t('ExampleModule.base', 'This Module shows some examples of the Module Interface.'),

        // Optional Section: Auto Imports when module is enabled
        'import' => array(
            'application.modules.example.*',
            [...]
        ),

        // Optional Section: Events to catch when module is enabled
        // Use this to modify e.g. menus 
        // http://www.yiiframework.com/doc/guide/1.1/en/basics.component#event
        'events' => array(
            // Listen for onInit Event of AdminMenuWidget and sent to 
            // Module Class File to handle it
            array('class' => 'AdminMenuWidget', 'event' => 'onInit', 
                  'callback' => array('ExampleModule', 'onAdminMenuInit')),
            
            [...]
        ),

        // Optional Section: When this module provides some modules to the user
        'userModules' => array(
        
            // Some unique id for a user module
            'example_someuser_feature' => array(
                
                // Basic Informations for the user
                'title' => Yii::t('ExampleModule.base','Shows Example Link on your profile'),
                'image' => '',
                'description' => Yii::t('ExampleModule.base', 'Awesome example link on your own profile!'),
            ),

            [...]
        ),

        // Optional Section: When this module provides some modules for spaces
        'spaceModules' => array(
        
            // Some unique id for a user module
            'example_somespace_feature' => array(
                
                // Basic Informations for the user
                'title' => Yii::t('ExampleModule.base','Shows Example Link on your space'),
                'image' => '',
                'description' => Yii::t('ExampleModule.base', 'Awesome example link on this space!'),
            ),

            [...]
        ),

    ));
    ?>