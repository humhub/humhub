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

### Limitations

The Zend Lucence Engine runs inside the PHP process and is limited by the
settings of the PHP environment in terms of memory usage and execution time.

By default Zend Lucence Engine sets a limit on the number of terms in a search query,
which also results in a limitation of the number of items a search term can match.

For the space search this must be set at least as high as the number of spaces.
In general the limit depends on the number of items a search term can match so it
highly depends on the content. To be sure all searches work you can set it higher than the
number of spaces/users/content you have.

It can be set to 0 for no limitation, but that may result in search queries
to fail caused by high memory usage.

You can [configure](advanced-configuration.md) the limit by setting `searchItemLimit` on the `search` application component:

```php
return [
    'components' => [
        'search' => [
            'searchItemLimit' => 10000,
        ],
    ],
];
```
