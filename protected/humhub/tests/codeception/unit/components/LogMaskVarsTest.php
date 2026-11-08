<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use Codeception\Test\Unit;
use humhub\components\log\Dispatcher;
use Yii;
use yii\log\EmailTarget;
use yii\log\Logger;
use yii\log\Target;

/**
 * Tests that secrets (e.g. loaded from `.env` into `$_SERVER`) are masked in the context dump of log entries.
 *
 * @see \yii\log\Target::getContextMessage()
 * @see Dispatcher
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

    public function testTargetsAddedOutsideCoreConfigAreMasked()
    {
        $_SERVER = array_merge($_SERVER, [
            'HUB_LOG_SMTP_PASSWORD' => 'smtp-pass-1',
            'MERCURE_SECRET_PUB' => 'mercure-2',
            'HTTP_X_HUMHUB_APP_TOKEN' => 'app-token-3',
        ]);
        $_POST = ['Login' => ['username' => 'jdoe', 'password' => 'post-pass-4']];
        $_COOKIE = ['PHPSESSID' => 'session-5'];

        // e.g. `HUMHUB_CONFIG__COMPONENTS__LOG__TARGETS__EMAIL`, with Yii's default `logVars` and `maskVars`
        $context = $this->getContextMessage([
            'class' => EmailTarget::class,
            'message' => ['to' => 'admin@example.com'],
        ]);

        foreach (['smtp-pass-1', 'mercure-2', 'app-token-3', 'post-pass-4', 'session-5'] as $value) {
            $this->assertStringNotContainsString($value, $context);
        }
        $this->assertStringContainsString('HUB_LOG_SMTP_PASSWORD', $context);
        $this->assertStringContainsString('PHPSESSID', $context);
        $this->assertStringContainsString('jdoe', $context);
    }

    public function testTargetsAddedAtRunTimeAreMasked()
    {
        $_SERVER['LATE_TARGET_SECRET'] = 'late-secret-1';

        // e.g. `Yii::$app->log->targets[] = …` in an event handler of a module, after the component was built
        $dispatcher = $this->createDispatcher();
        $dispatcher->targets['late'] = Yii::createObject([
            'class' => EmailTarget::class,
            'message' => ['to' => 'admin@example.com'],
            'enabled' => false,
        ]);
        $dispatcher->dispatch([], true);

        $context = $this->getTargetContext($dispatcher->targets['late']);

        $this->assertStringNotContainsString('late-secret-1', $context);
        $this->assertStringContainsString('LATE_TARGET_SECRET', $context);
    }

    /**
     * Creates the log dispatcher with the configuration of `config/common.php`, with its file and database target
     * or the given one. The targets are disabled so that nothing is written.
     */
    private function createDispatcher(?array $customTarget = null): Dispatcher
    {
        $config = require Yii::getAlias('@humhub/config/common.php');
        $logConfig = $config['components']['log'];
        if ($customTarget !== null) {
            $logConfig['targets'] = ['custom' => $customTarget];
        }
        $logConfig['targets'] = array_map(
            fn(array $target) => array_merge($target, ['enabled' => false]),
            $logConfig['targets'],
        );

        // Own logger: the dispatcher's constructor attaches itself to the given logger, and without one it would
        // take over the application's logger for the rest of the test run
        $logConfig['logger'] = new Logger();

        $dispatcher = Yii::createObject($logConfig);
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);

        return $dispatcher;
    }

    /**
     * Builds the context message of every target of the dispatcher created by [[createDispatcher()]]
     * and asserts that they mask identically.
     */
    private function getContextMessage(?array $customTarget = null): string
    {
        $contexts = [];
        foreach ($this->createDispatcher($customTarget)->targets as $target) {
            $contexts[] = $this->getTargetContext($target);
        }

        $this->assertCount(1, array_unique($contexts), 'All targets must mask identically');

        return $contexts[0];
    }

    private function getTargetContext(Target $target): string
    {
        return (fn() => $this->getContextMessage())->call($target);
    }
}
