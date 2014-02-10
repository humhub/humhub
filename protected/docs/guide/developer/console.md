Console
=======

Modules can also provide Commands to the Yiic Console Interface.

Intercept the ZmConsoleApplication onInit event to register own Commands.

Example:

    Yii::app()->interceptor->attachEventHandler('ZmConsoleApplication', 'onInit', array('MyModule', 'onConsoleApplicationStart'));


As example just look at the "generateData" Module which provides a dummy data generation command. 


