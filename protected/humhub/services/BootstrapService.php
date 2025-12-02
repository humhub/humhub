<?php

namespace humhub\services;

use humhub\helpers\ConfigHelper;
use humhub\helpers\DatabaseHelper;
use humhub\helpers\EnvHelper;
use Throwable;
use Yii;

final class BootstrapService
{
    private bool $debug;
    private string $humhubPath = __DIR__ . '/..';
    private string $configPath = __DIR__ . '/../../config';
    private string $vendorPath = __DIR__ . '/../../vendor';

    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;

        if (!empty($_ENV['HUMHUB_ALIASES__HUMHUB'])) {
            $this->humhubPath = $_ENV['HUMHUB_ALIASES__HUMHUB'];
        }
    }

    public function setPaths(?string $config = null, ?string $vendor = null, ?string $humhub = null): void
    {
        $this->configPath = $config ?: $this->configPath;
        $this->vendorPath = $vendor ?: $this->vendorPath;
        $this->humhubPath = $humhub ?: $this->humhubPath;
    }

    private function prepare()
    {
        $debug = filter_var($_ENV['HUMHUB_DEBUG'] ?? $this->debug, FILTER_VALIDATE_BOOLEAN);

        defined('YII_DEBUG') or define('YII_DEBUG', $debug);
        defined('YII_ENV') or define('YII_ENV', $debug ? 'dev' : 'prod');

        require($this->vendorPath . '/yiisoft/yii2/Yii.php');

        Yii::setAlias('@humhub', $this->humhubPath);
    }

    private function getConfig($mode = 'web'): array
    {
        $humhubConfig = [
            require($this->humhubPath . '/config/common.php'),
            require($this->humhubPath . '/config/' . $mode . '.php'),
        ];

        $commonConfig = [
            require($this->configPath . '/common.php'),
            require($this->configPath . '/' . $mode . '.php'),
            require($this->configPath . '/' . $mode . '.php'),
        ];

        $dynamicConfigFile = $this->configPath . '/dynamic.php';
        $dynamicConfig = (is_readable($dynamicConfigFile)) ? require($dynamicConfigFile) : [];

        return ConfigHelper::instance()
            ->setHumhub(...$humhubConfig)
            ->setDynamic($dynamicConfig)
            ->setCommon(...$commonConfig)
            ->setEnv(EnvHelper::toConfig($_ENV, \humhub\components\console\Application::class))
            ->toArray();
    }


    public function runWeb(): void
    {
        $this->prepare();

        try {
            (new \humhub\components\Application($this->getConfig('web')))->run();
        } catch (Throwable $e) {
            if (null === DatabaseHelper::handleConnectionErrors($e)) {
                throw $e;
            }
        }
    }

    public function runConsole(): void
    {
        $this->prepare();

        // fcgi doesn't have STDIN and STDOUT defined by default
        defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
        defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

        try {
            $exitCode = (new \humhub\components\console\Application($this->getConfig('console')))->run();
            exit($exitCode);
        } catch (\Throwable $e) {
            if (null === DatabaseHelper::handleConnectionErrors($e)) {
                throw $e;
            }
        }
    }

}
