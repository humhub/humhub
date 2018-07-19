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

or by means of [grunt](../developer/build.md):

```
grunt build-search
```

File Content Indexing
---------------------

In order to allow also indexing of file contents (e.g. PDF, Word or PowerPoint document) you can specify file parsers in your [configuration](advanced-configuration.md).

We recommend [Apache Tika](https://tika.apache.org/) as parser software which supports thousand of different file types.

Example configuration:

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
                                 'cmd' => '/usr/bin/java -jar /srv/www/var/lib/tika-app-1.18.jar --text {fileName} 2>/dev/null',
                                 'except' => ['image/']
                            ],
                         ]
                    ]
                ]
            ],
            
        ],
    ];
```


Zend Lucence Engine
--------------------

By default, HumHub is using a *Lucence* Index (Zend Lucence) to store search data.

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
