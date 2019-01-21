Oembed providers
======

HumHub provides an option for adding of additional OEmbed providers or override existing one. 
Advanced OEmbed providers can be configured, by changing application parameters within your common.php:

```php
    'modules' => [
        'user' => [
            'advancedOembedProviders' => [
                'providers' => [
                    'twitter.com' => 'https://publish.twitter.com/oembed?url=%url%&format=json'
                ],
                'override' => false
            ]
        ]
    ]
```

In case if override = false your providers will be added to existing OEmbed providers.

Also you can configure additional providers by setting up event listener in your module.