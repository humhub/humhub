# OEmbed Providers

HumHub renders external links via [oEmbed](https://oembed.com/) when a matching provider is configured. Modules can add or override providers by listening to `humhub\models\UrlOembed::FETCH`.

## Registering a provider

```php
// example/config.php
use humhub\models\UrlOembed;
use johndoe\example\Events;

return [
    'events' => [
        [
            'class' => UrlOembed::class,
            'event' => UrlOembed::FETCH,
            'callback' => [Events::class, 'onFetchOembed'],
        ],
    ],
];
```

```php
// example/Events.php
public static function onFetchOembed($event)
{
    $event->setProviders([
        'twitter.com' => 'https://publish.twitter.com/oembed?url=%url%&format=json',
    ]);
}
```

The key is the host pattern HumHub matches the URL against; the value is the provider endpoint URL with `%url%` substituted for the original URL. Multiple modules can register providers — later registrations override earlier matches for the same host.

The fetch happens server-side, on first encounter of the URL, and the result is cached.
