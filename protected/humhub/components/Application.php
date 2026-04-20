<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Exception;
use humhub\interfaces\ApplicationInterface;

/**
 * Description of Application
 *
 * @inheritdoc
 */
class Application extends \yii\web\Application implements ApplicationInterface
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

        parent::init();
        $this->trigger(self::EVENT_ON_INIT);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        /**
         * Check if it's already installed - if not force controller module
         */
        if (
            !$this->installationState->hasState(InstallationState::STATE_INSTALLED)
            && $this->controller && $this->controller->id != 'error'
            && $this->controller->module != null && $this->controller->module->id != 'installer'
        ) {
            $this->controller->redirect(['/installer/index']);

            return false;
        }

        /**
         * More random widget autoId prefix
         * Ensures to be unique also on ajax partials
         */
        \yii\base\Widget::$autoIdPrefix = 'h' . mt_rand(1, 999999) . 'w';

        return parent::beforeAction($action);
    }

    /**
     * Switch current language
     *
     * @param string $value
     */
    public function setLanguage($value)
    {
        if (!empty($value)) {
            $this->language = $value;
        }
    }
}
