Search System
=============

**The built in search system is used for:**
- Directory
- People/Space Search
- Content Search
- User/Space Picker Widgets


Index Rebuilding
----------------

If you need to rebuild the search index (e.g. after updating) you need to run following command:

```
cd /path/to/humhub/protected
php yii search/rebuild
```

or by means of [grunt](../developer/core-build.md):

```
grunt build-search
```

Zend Lucence Engine
--------------------

By default HumHub is using a *Lucence* Index (Zend Lucence) to store search data.

Default database folder: `/protected/runtime/searchdb/`

You can modify the default search directory in the [configuration](advanced-configuration.md):

```php
    return [
        // ...
        'params' => [
            'search' => [
                'zendLucenceDataDir' => '/some/other/path',
            ]
        ]
        // ...
    ];
```