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

    /**
     * Each `__` starts a new level, so a setting name must be written with single underscores to reach
     * `fixed-settings['base'][<setting>]`, which is where `SettingsManager::getFixed()` reads it.
     */
    public function testFixedSettings()
    {
        $ENV = [
            'HUMHUB_FIXED_SETTINGS__BASE__MAILER_DSN' => 'smtp://...',
            'HUMHUB_FIXED_SETTINGS__BASE__MAILER_TRANSPORT_TYPE' => 'php',
            'HUMHUB_FIXED_SETTINGS__BASE__MAILER_SYSTEM_EMAIL_ADDRESS' => 'noreply@humhub.com',
        ];

        $config = [
            'params' => [
                'fixed-settings' => [
                    'base' => [
                        'mailerDsn' => 'smtp://...',
                        'mailerTransportType' => 'php',
                        'mailerSystemEmailAddress' => 'noreply@humhub.com',
                    ],
                ],
            ],
        ];

        $this->assertEquals($config, EnvHelper::toConfig($ENV));
    }

    /**
     * The mailer settings were flattened in 1.18 (`m250226_125226_rename_mailer_vars`): the nested form still
     * parses, but it produces a key nothing reads, so fixing a setting that way silently has no effect.
     */
    public function testNestedFixedSettingsDoNotReachTheSettingName()
    {
        $ENV = [
            'HUMHUB_FIXED_SETTINGS__BASE__MAILER__SYSTEM_EMAIL_ADDRESS' => 'noreply@humhub.com',
        ];

        $config = [
            'params' => [
                'fixed-settings' => [
                    'base' => [
                        'mailer' => [
                            'systemEmailAddress' => 'noreply@humhub.com',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($config, EnvHelper::toConfig($ENV));
        $this->assertArrayNotHasKey(
            'mailerSystemEmailAddress',
            EnvHelper::toConfig($ENV)['params']['fixed-settings']['base'],
        );
    }

    public function testArrayConfig()
    {
        $ENV = [
            'HUMHUB_CONFIG__PARAMS__MODULE_AUTOLOAD_PATHS' => ["/app/modules/humhub","/app/modules/humhub-contrib"],
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
            'HUMHUB_CONFIG__COMPONENTS__DB' => '{"on afterOpen":["humhub\\\libs\\\Helpers","SqlMode"]}',
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

    public function testBooleanConfig()
    {
        $ENV = [
            'HUMHUB_CONFIG__COMPONENTS__URL_MANAGER__SHOW_SCRIPT_NAME' => 'false',
            'HUMHUB_CONFIG__COMPONENTS__URL_MANAGER__ENABLE_PRETTY_URL' => 'true',
        ];

        $config = [
            'components' => [
                'urlManager' => [
                    'showScriptName' => false,
                    'enablePrettyUrl' => true,
                ],
            ],
        ];

        $this->assertEquals($config, EnvHelper::toConfig($ENV));
    }

    public function testEmptyConfig()
    {
        $ENV = [
            'HUMHUB_CONFIG__COMPONENTS__URL_MANAGER__SHOW_SCRIPT_NAME' => 'false',
            'HUMHUB_CONFIG__COMPONENTS__URL_MANAGER__ENABLE_PRETTY_URL' => null,
        ];

        $config = [
            'components' => [
                'urlManager' => [
                    'showScriptName' => false,
                ],
            ],
        ];

        $this->assertEquals($config, EnvHelper::toConfig($ENV));
    }

    public function testWebApplicationConfig()
    {
        $ENV = [
            'HUMHUB_CONFIG__COMPONENTS__URL_MANAGER__SHOW_SCRIPT_NAME' => 'false',
            'HUMHUB_WEB_CONFIG__COMPONENTS__URL_MANAGER__SHOW_SCRIPT_NAME' => 'true',
            'HUMHUB_CLI_CONFIG__COMPONENTS__URL_MANAGER__SHOW_SCRIPT_NAME' => 'false',
        ];

        $config = [
            'components' => [
                'urlManager' => [
                    'showScriptName' => true,
                ],
            ],
        ];

        $this->assertEquals($config, EnvHelper::toConfig($ENV, \humhub\components\Application::class));
    }

    public function testConsoleApplicationConfig()
    {
        $ENV = [
            'HUMHUB_CONFIG__COMPONENTS__URL_MANAGER__SHOW_SCRIPT_NAME' => 'false',
            'HUMHUB_CLI_CONFIG__COMPONENTS__URL_MANAGER__SHOW_SCRIPT_NAME' => 'true',
            'HUMHUB_WEB_CONFIG__COMPONENTS__URL_MANAGER__SHOW_SCRIPT_NAME' => 'false',
        ];

        $config = [
            'components' => [
                'urlManager' => [
                    'showScriptName' => true,
                ],
            ],
        ];

        $this->assertEquals($config, EnvHelper::toConfig($ENV, \humhub\components\console\Application::class));
    }
}
