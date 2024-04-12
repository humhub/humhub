<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\console;

use humhub\components\ApplicationTrait;
use humhub\interfaces\ApplicationInterface;
use Yii;
use yii\console\Exception;

/**
 * Description of Application
 *
 * @inheritdoc
 */
class Application extends \yii\console\Application implements ApplicationInterface
{
    use ApplicationTrait;

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (version_compare(phpversion(), $this->minSupportedPhpVersion, '<')) {
            throw new Exception(sprintf(
                'Installed PHP Version is too old! Required minimum version is PHP %s (Installed: %s)',
                $this->minSupportedPhpVersion,
                phpversion(),
            ));
        }

        if ($this->isDatabaseInstalled(true)) {
            $baseUrl = $this->settings->get('baseUrl');
            if (!empty($baseUrl)) {
                if (Yii::getAlias('@web', false) === false) {
                    Yii::setAlias('@web', $baseUrl);
                }
                if (Yii::getAlias('@web-static', false) === false) {
                    Yii::setAlias('@web-static', '@web/static');
                }
                if (Yii::getAlias('@webroot-static', false) === false) {
                    Yii::setAlias('@webroot-static', '@webroot/static');
                }
            }
        }

        parent::init();
        $this->trigger(self::EVENT_ON_INIT);
    }

    /**
     * Returns the configuration of the built-in commands.
     *
     * @return array the configuration of the built-in commands.
     */
    public function coreCommands()
    {
        return [
            'help' => 'yii\console\controllers\HelpController',
            'cache' => 'yii\console\controllers\CacheController',
            'asset' => 'yii\console\controllers\AssetController',
            'fixture' => 'yii\console\controllers\FixtureController',
        ];
    }
}
