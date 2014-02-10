Modules - General
=================

See http://www.yiiframework.com/doc/guide/1.1/en/basics.module for general information
about modules in Yii. 

As different modules could be dynamically loaded and enabled there is no need
to add them into a global configuration file like main.php.

Module Folder Structure
------------------------
    /modules/                           - Modules Base Folder
        /mymodule/                      - My Module Id
            MyModule.php                - Base Module Class 
            autostart.php               - Holds basic definition/config of module
            /views/                     - Views Folder
            /controllers/               - Controllers Folder
            /models/                    - Models Folder
            ...




