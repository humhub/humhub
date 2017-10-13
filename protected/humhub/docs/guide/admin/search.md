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


File Indexing
-------------

If you like to also index contents of a file (e.g. PDF documents) you need to specify additional parsers.

These parsers are defined in the [configuration file](advanced-configuration.md).

Parsers:
- Apache Tika (https://tika.apache.org/)
- Poppler PDF Utils (https://poppler.freedesktop.org/)


Example:

```php
return [
    // ...
    'modules' => [
        // ...
        'file' => [
            'converterOptions' => [
                'humhub\modules\file\converter\TextConverter' => [
                    'converter' => [
                        [
                            'cmd' => '/usr/bin/pdftotext -q -enc UTF-8 {fileName} {outputFileName}',
                            'only' => ['pdf']
                        ],
                        [
                            'cmd' => '/usr/bin/java -jar /opt/tika-app-1.16.jar --text {fileName} 2>/dev/null',
                            'except' => ['image/']
                        ]
                    ]
                ]
            ]
        ]
        // ...
    ],
    // ...
];
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