<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\web;

use humhub\modules\web\pwa\controllers\ManifestController;
use humhub\modules\web\pwa\controllers\OfflineController;
use humhub\modules\web\pwa\controllers\ServiceWorkerController;
use Yii;

/**
 * This module provides general web components.
 *
 * @since 1.4
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * @inheritdoc
     */
    public $controllerMap = [
        'pwa-manifest' => ManifestController::class,
        'pwa-offline' => OfflineController::class,
        'pwa-service-worker' => ServiceWorkerController::class
    ];

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('WebModule.base', 'Web');
    }

}
