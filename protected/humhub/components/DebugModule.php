<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\helpers\ArrayHelper;
use humhub\helpers\Html;
use humhub\services\BootstrapService;
use Yii;

class DebugModule extends \yii\debug\Module
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->setBasePath(Yii::getAlias('@yii/debug'));
    }

    /**
     * @inheritdoc
     */
    public function renderToolbar($event)
    {
        ob_start();
        parent::renderToolbar($event);
        echo str_replace('<script', '<script ' . Html::nonce(), ob_get_clean());
    }

    /**
     * @inheritdoc
     */
    protected function resetGlobalSettings()
    {
        parent::resetGlobalSettings();

        // Clear all assets in order to don't append them to the debug iframe
        Yii::$app->view->clear();

        // Restore asset bundles from config
        $webConfig = (new BootstrapService())->getConfig('web');
        if (isset($webConfig['components']['assetManager']['bundles'])) {
            Yii::$app->assetManager->bundles = ArrayHelper::merge(
                Yii::$app->assetManager->bundles,
                $webConfig['components']['assetManager']['bundles'],
            );
        }
    }
}
