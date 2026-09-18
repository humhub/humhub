<?php

namespace humhub\services;

use humhub\helpers\ConfigHelper;
use humhub\helpers\DatabaseHelper;
use humhub\helpers\EnvHelper;
use Throwable;
use Yii;

final class BootstrapService
{
    private string $humhubPath = __DIR__ . '/..';
    private string $configPath = __DIR__ . '/../../../config';
    private string $legacyConfigPath = __DIR__ . '/../../config';
    private string $vendorPath = __DIR__ . '/../../vendor';

    public function __construct(private readonly bool $debug = false)
    {
        if (!empty($_ENV['HUMHUB_ALIASES__HUMHUB'])) {
            $this->humhubPath = $_ENV['HUMHUB_ALIASES__HUMHUB'];
        }
    }

    public function setPaths(
        ?string $config = null,
        ?string $vendor = null,
        ?string $humhub = null,
        ?string $legacyConfig = null,
    ): void {
        $this->configPath = $config ?: $this->configPath;
        $this->vendorPath = $vendor ?: $this->vendorPath;
        $this->humhubPath = $humhub ?: $this->humhubPath;
        $this->legacyConfigPath = $legacyConfig ?: $this->legacyConfigPath;
    }

    private function prepare()
    {
        $debug = filter_var($_ENV['HUMHUB_DEBUG'] ?? $this->debug, FILTER_VALIDATE_BOOLEAN);

        defined('YII_DEBUG') or define('YII_DEBUG', $debug);
        defined('YII_ENV') or define('YII_ENV', $debug ? 'dev' : 'prod');

        require($this->vendorPath . '/yiisoft/yii2/Yii.php');

        Yii::setAlias('@humhub', $this->humhubPath);
    }

    public function getConfig($mode = 'web'): array
    {
        $appClass = $mode === 'console'
            ? \humhub\components\console\Application::class
            : \humhub\components\Application::class;

        $humhubConfig = [
            require($this->humhubPath . '/config/common.php'),
            require($this->humhubPath . '/config/' . $mode . '.php'),
        ];

        // The configuration directory moved into the installation root in 1.20. The old one below
        // `protected/` is still read so installations keep working across the update; the new one
        // is merged last so a half-migrated installation can override what it has not moved yet.
        $commonConfig = [
            $this->requireConfig($this->legacyConfigPath . '/common.php'),
            $this->requireConfig($this->legacyConfigPath . '/' . $mode . '.php'),
            $this->requireConfig($this->configPath . '/common.php'),
            $this->requireConfig($this->configPath . '/' . $mode . '.php'),
        ];

        $dynamicConfig = [
            $this->requireConfig($this->legacyConfigPath . '/dynamic.php'),
            $this->requireConfig($this->configPath . '/dynamic.php'),
        ];

        return ConfigHelper::instance()
            ->setHumhub(...$humhubConfig)
            ->setDynamic(...$dynamicConfig)
            ->setCommon(...$commonConfig)
            ->setEnv(EnvHelper::toConfig($_ENV, $appClass))
            ->toArray();
    }

    /**
     * @return array the configuration in the given file - every local configuration file is
     *               optional, and none of them is shipped
     */
    private function requireConfig(string $file): array
    {
        if (!is_readable($file)) {
            return [];
        }

        $config = require($file);

        return is_array($config) ? $config : [];
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
