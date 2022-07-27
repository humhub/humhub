<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\modules\admin\models\forms\MailingSettingsForm;
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
        $config['name'] = Yii::$app->settings->get('name');

        // Add Default language
        $defaultLanguage = Yii::$app->settings->get('defaultLanguage');
        if ($defaultLanguage !== null && $defaultLanguage != '') {
            $config['language'] = Yii::$app->settings->get('defaultLanguage');
        } else {
            $config['language'] = Yii::$app->language;
        }

        $defaultTimeZone = Yii::$app->settings->get('defaultTimeZone');
        if (!empty($defaultTimeZone)) {
            $config['timeZone'] = $defaultTimeZone;
            $config['components']['formatter']['defaultTimeZone'] = $defaultTimeZone;
        }

        // Add Caching
        $cacheClass = Yii::$app->settings->get('cache.class');
        if (in_array($cacheClass, ['yii\caching\DummyCache', 'yii\caching\FileCache'])) {
            $config['components']['cache'] = [
                'class' => $cacheClass,
                'keyPrefix' => Yii::$app->id
            ];
        } elseif ($cacheClass == 'yii\caching\ApcCache' && (function_exists('apcu_add') || function_exists('apc_add'))) {
            $config['components']['cache'] = [
                'class' => $cacheClass,
                'keyPrefix' => Yii::$app->id,
                'useApcu' => (function_exists('apcu_add'))
            ];
        } elseif ($cacheClass === \yii\redis\Cache::class) {
            $config['components']['cache'] = [
                'class' => \yii\redis\Cache::class,
                'keyPrefix' => Yii::$app->id
            ];
        }

        // Add User settings
        $config['components']['user'] = [];
        if (Yii::$app->getModule('user')->settings->get('auth.defaultUserIdleTimeoutSec')) {
            $config['components']['user']['authTimeout'] = Yii::$app->getModule('user')->settings->get('auth.defaultUserIdleTimeoutSec');
        }

        // Install Mail Component
        $config['components']['mailer'] = self::getMailerConfig();

        // Remove old theme/view stuff
        unset($config['components']['view']);
        unset($config['components']['mailer']['view']);

        // Cleanups
        unset($config['components']['db']['charset']);
        unset($config['components']['formatterApp']);

        $config['params']['config_created_at'] = time();
        $config['params']['horImageScrollOnMobile'] = Yii::$app->settings->get('horImageScrollOnMobile');

        self::save($config);
    }

    private static function getMailerConfig()
    {
        $mail = [];
        $mail['transport'] = [];

        $transportType = Yii::$app->settings->get('mailer.transportType', MailingSettingsForm::TRANSPORT_PHP);

        if ($transportType === MailingSettingsForm::TRANSPORT_SMTP) {
            if (Yii::$app->settings->get('mailer.hostname')) {
                $mail['transport']['host'] = Yii::$app->settings->get('mailer.hostname');
            }
            if (Yii::$app->settings->get('mailer.port')) {
                $mail['transport']['port'] = (int)Yii::$app->settings->get('mailer.port');
            } else {
                $mail['transport']['port'] = 25;
            }
            if (Yii::$app->settings->get('mailer.username')) {
                $mail['transport']['username'] = Yii::$app->settings->get('mailer.username');
            }
            if (Yii::$app->settings->get('mailer.password')) {
                $mail['transport']['password'] = Yii::$app->settings->get('mailer.password');
            }
            $mail['transport']['scheme'] = (empty(Yii::$app->settings->get('mailer.useSmtps'))) ? 'smtp' : 'smtps';

        } elseif ($transportType === MailingSettingsForm::TRANSPORT_CONFIG) {
            return [];
        } elseif ($transportType === MailingSettingsForm::TRANSPORT_PHP) {
            $mail['transport']['dsn'] = 'native://default';
        } elseif ($transportType === MailingSettingsForm::TRANSPORT_DSN) {
            $mail['transport']['dsn'] = Yii::$app->settings->get('mailer.dsn');
        } elseif ($transportType === MailingSettingsForm::TRANSPORT_FILE) {
            $mail['useFileTransport'] = true;
        }

        return $mail;
    }

    /**
     * Checks whether the config should be rewritten based on changed setting name
     *
     * @param $moduleId
     * @param $name
     * @return bool
     */
    public static function needRewrite($moduleId, $name)
    {
        return (in_array($name, [
            'name', 'defaultLanguage', 'timeZone', 'cache.class', 'mailer.transportType',
            'mailer.hostname', 'mailer.username', 'mailer.password', 'mailer.encryption',
            'mailer.port', 'horImageScrollOnMobile']));
    }

    public static function getConfigFilePath()
    {
        return Yii::getAlias(Yii::$app->params['dynamicConfigFile']);
    }
}
