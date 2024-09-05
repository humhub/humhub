<?php

namespace tests\codeception\unit\modules\web\env;

use humhub\helpers\EnvHelper;
use tests\codeception\_support\HumHubDbTestCase;

class ConfigTest extends HumHubDbTestCase
{
    public function testDebugIgnored()
    {
        $ENV = [
            'HUMHUB_DEBUG' => 1,
        ];

        $this->assertEmpty(EnvHelper::toConfig($ENV));
    }

    public function testFixedSettings()
    {
        $ENV = [
            'HUMHUB_FIXED_SETTINGS.BASE.MAILER.DSN' => 'smtp://...',
            'HUMHUB_FIXED_SETTINGS.BASE.MAILER.TRANSPORT_TYPE' => 'php',
            'HUMHUB_FIXED_SETTINGS.BASE.MAILER.SYSTEM_EMAIL_ADDRESS' => 'noreply@humhub.com',
        ];

        $config = [
            'params' => [
                'fixed-settings' => [
                    'base' => [
                        'mailer' => [
                            'dsn' => 'smtp://...',
                            'transportType' => 'php',
                            'systemEmailAddress' => 'noreply@humhub.com',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($config, EnvHelper::toConfig($ENV));
    }

    public function testArrayConfig()
    {
        $ENV = [
            'HUMHUB_CONFIG.PARAMS.MODULE_AUTOLOAD_PATHS' => ["/app/modules/humhub","/app/modules/humhub-contrib"],
        ];

        $config = [
            'params' => [
                'moduleAutoloadPaths' => [
                    '/app/modules/humhub',
                    '/app/modules/humhub-contrib',
                ],
            ],
        ];

        $this->assertEquals($config, EnvHelper::toConfig($ENV));
    }

    public function testJsonConfig()
    {
        $ENV = [
            'HUMHUB_CONFIG.COMPONENTS.DB' => '{"on afterOpen":["humhub\\\libs\\\Helpers","SqlMode"]}',
        ];

        $config = [
            'components' => [
                'db' => [
                    'on afterOpen' => ['humhub\libs\Helpers', 'SqlMode'],
                ],
            ],
        ];

        $this->assertEquals($config, EnvHelper::toConfig($ENV));
    }
}
