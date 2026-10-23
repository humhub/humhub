<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use Codeception\Test\Unit;
use Yii;
use yii\log\DbTarget;
use yii\log\FileTarget;

/**
 * Tests that secrets (e.g. loaded from `.env` into `$_SERVER`) are masked in the context dump of log entries.
 *
 * @see \yii\log\Target::getContextMessage()
 */
class LogMaskVarsTest extends Unit
{
    private array $serverBackup;
    private array $getBackup;

    protected function _before()
    {
        $this->serverBackup = $_SERVER;
        $this->getBackup = $_GET;
    }

    protected function _after()
    {
        $_SERVER = $this->serverBackup;
        $_GET = $this->getBackup;
    }

    public function testSecretsAreMasked()
    {
        $secrets = [
            'HUMHUB_CONFIG__COMPONENTS__DB__PASSWORD' => 'db-pass-1',
            'HUMHUB_CONFIG__COMPONENTS__FS__MOUNTS__S3__CLIENT_CONFIG__CREDENTIALS__KEY' => 's3-key-2',
            'HUMHUB_CONFIG__COMPONENTS__FS__MOUNTS__S3__CLIENT_CONFIG__CREDENTIALS__SECRET' => 's3-secret-3',
            'HUMHUB_FIXED_SETTINGS__BASE__MAILER_DSN' => 'smtp://user:dsn-pass-4@mail.example.com',
            'MODULE_OAUTH_CLIENT_SECRET' => 'oauth-5',
            'MODULE_API_TOKEN' => 'token-6',
            'SMTP_PASSPHRASE' => 'passphrase-7',
            'my_private_value' => 'private-8',
            'HTTP_AUTHORIZATION' => 'Bearer auth-9',
            'PHP_AUTH_USER' => 'auth-user-10',
            'PHP_AUTH_PW' => 'auth-pw-11',
            'HTTP_COOKIE' => 'PHPSESSID=cookie-12',
        ];
        $_SERVER = array_merge($_SERVER, $secrets);
        $_GET = ['token' => 'get-token-13', 'password' => 'get-pass-14'];

        $context = $this->getContextMessage();

        foreach ($secrets as $name => $value) {
            $this->assertStringContainsString($name, $context, "Key $name should still be listed");
            $this->assertStringNotContainsString($value, $context, "Value of $name must be masked");
        }
        $this->assertStringNotContainsString('get-token-13', $context);
        $this->assertStringNotContainsString('get-pass-14', $context);
        $this->assertStringContainsString('***', $context);
    }

    public function testNonSecretsAreKept()
    {
        $_SERVER = array_merge($_SERVER, [
            'REQUEST_URI' => '/index.php?r=dashboard',
            'REQUEST_METHOD' => 'POST',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 LogMaskVarsTest',
            'REMOTE_ADDR' => '203.0.113.7',
        ]);
        $_GET = ['r' => 'dashboard/dashboard'];

        $context = $this->getContextMessage();

        $this->assertStringContainsString('/index.php?r=dashboard', $context);
        $this->assertStringContainsString('POST', $context);
        $this->assertStringContainsString('Mozilla/5.0 LogMaskVarsTest', $context);
        $this->assertStringContainsString('203.0.113.7', $context);
        $this->assertStringContainsString('dashboard/dashboard', $context);
    }

    /**
     * Builds the context message with the log target configuration of `config/common.php`
     * (shared by the file and the database target).
     */
    private function getContextMessage(): string
    {
        $config = require Yii::getAlias('@humhub/config/common.php');

        foreach ([FileTarget::class, DbTarget::class] as $class) {
            $targetConfig = $config['components']['log']['targets'][$class];
            unset($targetConfig['class']);

            $target = new class($targetConfig) extends FileTarget {
                public function context(): string
                {
                    return $this->getContextMessage();
                }
            };
            $contexts[] = $target->context();
        }

        $this->assertSame($contexts[0], $contexts[1], 'File and DB target must mask identically');

        return $contexts[0];
    }
}
