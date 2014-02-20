Events
======

To extends HumHub core functions you are able to listen to events and interact with it.
Each module can define some events in its autostart.php and intercept them.

``Example:``

    Yii::app()->interceptor->preattachEventHandler('ClassWhichFiresTheEvent', 'onMyEvent', array('MyModule', 'EventInterceptMethod'));