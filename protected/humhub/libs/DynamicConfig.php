<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\helpers\ArrayHelper;
use humhub\components\Theme;
use humhub\models\Setting;

/**
 * DynamicConfig provides access to the dynamic configuration file.
 *
 * @author luke
 */
class DynamicConfig extends \yii\base\Object
{

    /**
     * Add an array to the dynamic configuration
     * 
     * @param array $new
     */
    public static function merge($new)
    {
        $config = \yii\helpers\ArrayHelper::merge(self::load(), $new);
        self::save($config);
    }

    /**
     * This method is called when a a setting is changed.
     * 
     * @see Setting
     * @param Setting $setting
     */
    public static function onSettingChange($setting)
    {
        $config = self::load();
        self::setSettingValue($config['params'], $setting);
        self::save($config);
    }

    public static function setSettingValue(&$config, $setting)
    {

        $moduleId = $setting->module_id;
        if ($moduleId == '') {
            $moduleId = 'core';
        }

        $value = '';
        if ($setting->value_text != '') {
            $value = (string) $setting->value_text;
        } else {
            $value = (string) $setting->value;
        }

        $config['settings'][$moduleId][$setting->name] = $value;
        Yii::$app->params['settings'][$moduleId][$setting->name] = $value;
    }

    public static function getSettingValue($name, $moduleId)
    {
        if ($moduleId == '') {
            $moduleId = 'core';
        }

        if (isset(Yii::$app->params['settings'][$moduleId][$name])) {
            return Yii::$app->params['settings'][$moduleId][$name];
        }

        return null;
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

        // Load config file with file_get_contents and eval, cause require don't reload
        // the file when it's changed on runtime
        $configContent = str_replace(['<' . '?php', '<' . '?', '?' . '>'], '', file_get_contents($configFile));
        $config = eval($configContent);

        if (!is_array($config))
            return array();

        return $config;
    }

    /**
     * Sets a new dynamic configuration
     * 
     * @param array $config
     */
    public static function save($config)
    {
        $content = "<" . "?php return ";
        $content .= var_export($config, true);
        $content .= "; ?" . ">";

        $configFile = self::getConfigFilePath();
        file_put_contents($configFile, $content);

        if (function_exists('opcache_invalidate')) {
            opcache_reset();
            opcache_invalidate($configFile);
        }

        if (function_exists('apc_compile_file')) {
            apc_compile_file($configFile);
        }
    }

    /**
     * Rewrites DynamicConfiguration based on Database Stored Settings
     */
    public static function rewrite()
    {

        // Get Current Configuration
        $config = self::load();

        // Add Application Name to Configuration
        $config['name'] = Setting::Get('name');

        // Add Default language
        $defaultLanguage = Setting::Get('defaultLanguage');
        if ($defaultLanguage !== null && $defaultLanguage != "") {
            $config['language'] = Setting::Get('defaultLanguage');
        } else {
            $config['language'] = Yii::$app->language;
        }

        $timeZone = Setting::Get('timeZone');
        if ($timeZone != "") {
            $config['timeZone'] = $timeZone;
            $config['components']['formatter']['defaultTimeZone'] = $timeZone;
            $config['components']['formatterApp']['defaultTimeZone'] = $timeZone;
            $config['components']['formatterApp']['timeZone'] = $timeZone;
        }

        // Add Caching
        $cacheClass = Setting::Get('type', 'cache');
        if (in_array($cacheClass, ['yii\caching\DummyCache', 'yii\caching\ApcCache', 'yii\caching\FileCache'])) {
            $config['components']['cache'] = [
                'class' => $cacheClass,
                'keyPrefix' => Yii::$app->id
            ];

            // Prefix APC Cache Keys
            //if ($cacheClass == 'yii\caching\ApcCache') {
            //    $config['components']['cache'] = [
            //        'keyPrefix' => Yii::$app->id
            //    ];
            //}
        }
        // Add User settings
        $config['components']['user'] = array();
        if (Setting::Get('defaultUserIdleTimeoutSec', 'authentication_internal')) {
            $config['components']['user']['authTimeout'] = Setting::Get('defaultUserIdleTimeoutSec', 'authentication_internal');
        }

        // Install Mail Component
        $mail = [];
        $mail['transport'] = array();
        if (Setting::Get('transportType', 'mailing') == 'smtp') {
            $mail['transport']['class'] = 'Swift_SmtpTransport';

            if (Setting::Get('hostname', 'mailing'))
                $mail['transport']['host'] = Setting::Get('hostname', 'mailing');

            if (Setting::Get('username', 'mailing'))
                $mail['transport']['username'] = Setting::Get('username', 'mailing');

            if (Setting::Get('password', 'mailing'))
                $mail['transport']['password'] = Setting::Get('password', 'mailing');

            if (Setting::Get('encryption', 'mailing'))
                $mail['transport']['encryption'] = Setting::Get('encryption', 'mailing');

            if (Setting::Get('port', 'mailing'))
                $mail['transport']['port'] = Setting::Get('port', 'mailing');

            /*
              if (Setting::Get('allowSelfSignedCerts', 'mailing')) {
              $mail['transport']['ssl']['allow_self_signed'] = true;
              $mail['transport']['ssl']['verify_peer'] = false;
              }
             */
        } elseif (Setting::Get('transportType', 'mailing') == 'php') {
            $mail['transport']['class'] = 'Swift_MailTransport';
        } else {
            $mail['useFileTransport'] = true;
        }
        $config['components']['mailer'] = $mail;
        $config = ArrayHelper::merge($config, Theme::getThemeConfig(Setting::Get('theme')));
        $config['params']['config_created_at'] = time();

        foreach (Setting::find()->all() as $setting) {
            self::setSettingValue($config['params'], $setting);
        }

        self::save($config);
    }

    public static function getConfigFilePath()
    {
        return Yii::getAlias(Yii::$app->params['dynamicConfigFile']);
    }

}
