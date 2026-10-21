<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\web;

use Yii;
use humhub\modules\web\security\controllers\ReportController;
use humhub\modules\web\pwa\controllers\ManifestController;
use humhub\modules\web\pwa\controllers\OfflineController;
use humhub\modules\web\pwa\controllers\ServiceWorkerController;

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
     * @var mixed web security settings
     */
    public $security;

    /**
     * @since 1.8
     * @var bool Disable Service Worker and PWA Support
     */
    public $enableServiceWorker = true;

    /**
     * @var bool sends a Content-Security-Policy-Report-Only header with the policy of [[cspReportOnly]],
     * to see which resources a stricter policy would block before enforcing it
     * @since 1.19.1
     */
    public $enableCspReportOnly = false;

    /**
     * @var string policy of the Content-Security-Policy-Report-Only header, sent when [[enableCspReportOnly]] is set.
     * `{{ nonce }}` is replaced with the script nonce of the session, `{{ reporting }}` with the
     * `report-uri` and `report-to` directives pointing to the report endpoint.
     * @since 1.19.1
     */
    public $cspReportOnly = "script-src {{ nonce }} 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'; {{ reporting }}";

    /**
     * @inheritdoc
     */
    public $controllerMap = [
        'pwa-manifest' => ManifestController::class,
        'pwa-offline' => OfflineController::class,
        'pwa-service-worker' => ServiceWorkerController::class,
        'security-report' => ReportController::class,
    ];

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('WebModule.base', 'Web');
    }

}
