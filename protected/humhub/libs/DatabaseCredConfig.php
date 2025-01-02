<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\components\InstallationState;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 * DynamicConfig provides access to the dynamic configuration file.
 *
 * @todo check modules too
 *
 * @author luke
 */
class DatabaseCredConfig extends BaseObject
{
    /**
     * Returns the dynamic configuration
     *
     * @return array
     */
    public static function load()
    {
        $configFile = self::getConfigFilePath();

        if (!is_file($configFile)) {
            self::save([]);
        }

        // Load config file with 'file_get_contents' and 'eval'
        // because 'require' don't reload the file when it's changed on runtime
        $configContent = str_replace(['<' . '?php', '<' . '?', '?' . '>'], '', file_get_contents($configFile));
        $config = eval($configContent);

        if (!is_array($config)) {
            return [];
        }

        if (Yii::$app->installationState->hasState(InstallationState::STATE_DATABASE_CONFIGURED)) {
            $validConfig = [
                'components' => [
                    'db' => ArrayHelper::getValue($config, 'components.db', []),
                ],
            ];

            if ($validConfig != $config) {
                self::save($validConfig);
            }

            return $validConfig;
        }

        return $config;
    }

    /**
     * Sets a new dynamic configuration
     *
     * @param array $config
     */
    public static function save($config)
    {
        $content = '<' . '?php return ';
        $content .= var_export($config, true);
        $content .= ';';

        $configFile = self::getConfigFilePath();
        file_put_contents($configFile, $content);

        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($configFile);
        }

        if (function_exists('apc_compile_file')) {
            apc_compile_file($configFile);
        }
    }

    /**
     * Rewrites DynamicConfiguration based on Database Stored Settings
     *
     * @deprecated since 1.8
     */
    public static function rewrite()
    {
    }

    public static function getConfigFilePath()
    {
        return Yii::getAlias(Yii::$app->params['dynamicConfigFile']);
    }

    public static function exist()
    {
        return file_exists(self::getConfigFilePath());
    }
}
