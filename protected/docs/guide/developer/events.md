Events
======

To extends zamm core functions you are able to listen to events and interact with it.

Each module can define some events in its autostart.php and intercept them.


``Example:``

    Yii::app()->interceptor->attachEventHandler('ClassWhichFiresTheEvent', 'onMyEvent', array('MyModule', 'EventInterceptMethod'));