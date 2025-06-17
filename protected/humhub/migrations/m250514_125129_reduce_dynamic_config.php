<?php

use yii\db\Migration;
use yii\helpers\ArrayHelper;

class m250514_125129_reduce_dynamic_config extends Migration
{
    public function safeUp()
    {
        if (ArrayHelper::getValue(Yii::$app->params, 'installed')) {
            Yii::$app->installationState->setInstalled();

            /**
             * Reduce all automatically inserted config values
             */
            if (!YII_ENV_TEST) {
                $configFile = Yii::getAlias(Yii::$app->params['dynamicConfigFile']);

                if (!file_exists($configFile)) {
                    Yii::error('Could not migrate DynamicConfig. File does not exists: ' . $configFile);
                    ;
                }

                if (!is_writable($configFile)) {
                    Yii::error('Could not migrate DynamicConfig. File is not writable: ' . $configFile);
                }

                // Load config file with 'file_get_contents' and 'eval'
                // because 'require' don't reload the file when it's changed on runtime
                $configContent = str_replace(
                    ['<' . '?php', '<' . '?', '?' . '>'],
                    '',
                    file_get_contents($configFile),
                );
                $config = eval($configContent);

                unset($config['components']['cache']);
                unset($config['components']['user']);
                unset($config['components']['mailer']);
                unset($config['components']['view']);
                unset($config['components']['mailer']['view']);
                unset($config['components']['db']['charset']);
                unset($config['components']['formatterApp']);
                unset($config['timeZone']);
                unset($config['language']);
                unset($config['components']['formatter']);
                unset($config['params']['config_created_at']);
                unset($config['params']['horImageScrollOnMobile']);
                unset($config['params']['databaseInstalled']);
                unset($config['params']['installed']);
                unset($config['params']['installer']);
                unset($config['name']);

                $content = '<' . '?php return ';
                $content .= var_export($config, true);
                $content .= '; ?' . '>';

                file_put_contents($configFile, $content);

                if (function_exists('opcache_invalidate')) {
                    @opcache_invalidate($configFile);
                }

                if (function_exists('apc_compile_file')) {
                    apc_compile_file($configFile);
                }
            }
        }
    }

    public function safeDown()
    {
        echo "m250514_125129_reduce_dynamic_config cannot be reverted.\n";

        return false;
    }
}
