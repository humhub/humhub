Updating to 1.4
===============

> NOTE: This guide only affects updates from HumHub 1.3.x or lower to HumHub 1.4


1.) Please check following guides if you're using custom modules or themes:
- [Theme Migration Guide](../theme/migrate.md)
- [Module Migration Guide](../developer/modules-migrate.md)


2.) Language codes

Some language codes have changed. If you use any codes in configuration files or in manually overwritten translations, please check if they are affected. 

Affected codes:

| Old language code| New language code |
|----------|-------------|
| en | en-US |
| en_gb | en-GB |
| pt_br | pt-BR |
| nb_no | nb-NO |
| nn_no | nn-NO |
| zh_cn | zh-CN |
| zh_tw | zh-TW |
| fa_ir | fa-IR |

3.) The notification target configuration changed from:

```
'targets' => [ 
    \humhub\modules\notification\targets\WebTarget::class => [
        'class' => \humhub\modules\notification\targets\WebTarget::class
        'renderer' => ['class' => \humhub\modules\notification\renderer\WebRenderer::class]
    ],
...
]
```

to:

```
'targets' => [ 
    \humhub\modules\notification\targets\WebTarget::class => [
        'renderer' => ['class' => \humhub\modules\notification\renderer\WebRenderer::class]
    ],
...
]
```

Notification targets now can be overwritten or disabled e.g:

```
return [
    'components' => [
        'targets' => [ 
            \humhub\modules\notification\targets\MailTarget::class => [
                'active' => false
            ],
            \humhub\modules\notification\targets\MobileTarget::class => [
                'class' => '/my/own/target/MobileTarget'
            ],
        ]
    ]
]
```
