Oembed providers
======

HumHub provides an option for adding of additional OEmbed providers or override existing one. 
Advanced OEmbed providers can be configured, by setting up event listener in your module config.php:

### Catching an Event

Example event section of the config.php file:

```php
'events' => [
    [
        'class' => \humhub\models\UrlOembed::class, 
        'event' => \humhub\models\UrlOembed::FETCH, 
        'callback' => [Events::class, 'onFetchOembed'],
    ], 
 ]
```

### Processing 

Example of event callback:

```php
public static function onFetchOembed($event)
{
    $event->setProviders([
        'twitter.com' => 'https://publish.twitter.com/oembed?url=%url%&format=json'
    ]);
}
```