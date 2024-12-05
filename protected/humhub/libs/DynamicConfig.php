<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 * DynamicConfig provides access to the dynamic configuration file.
 *
 * @author luke
 */
class DynamicConfig extends BaseObject
{
    /**
     * Add an array to the dynamic configuration
     *
     * @param array $new
     */
    public static function merge($new)
    {
        $config = ArrayHelper::merge(self::load(), $new);
        self::save($config);
    }

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
        $content .= '; ?' . '>';

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
        // Get Current Configuration
        $config = self::load();
        $config['params']['horImageScrollOnMobile'] = Yii::$app->settings->get('horImageScrollOnMobile');

        self::save($config);
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
