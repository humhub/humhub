Events
======

We are using events to allow modules to extend core functionalities.

See Yii Framework Documentation for more details about events. 
<http://www.yiiframework.com/doc/guide/1.1/en/basics.component#event>

In addition to Yii build in events, we are using the ``controller-events`` extension,
to also allow controller events. <http://www.yiiframework.com/extension/controller-events/>

Each module can define a set of event listeners in modules ``autostart.php`` file.

```php
return array(
    // ..
    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('LikeModule', 'onUserDelete')),
        array('class' => 'HActiveRecordContent', 'event' => 'onBeforeDelete', 'callback' => array('LikeModule', 'onContentDelete')),
        array('class' => 'HActiveRecordContentAddon', 'event' => 'onBeforeDelete', 'callback' => array('LikeModule', 'onContentAddonDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('LikeModule', 'onIntegrityCheck')),
        array('class' => 'WallEntryLinksWidget', 'event' => 'onInit', 'callback' => array('LikeModule', 'onWallEntryLinksInit')),
        array('class' => 'WallEntryAddonWidget', 'event' => 'onInit', 'callback' => array('LikeModule', 'onWallEntryAddonInit')),
    ),
    // ...
);
```

```