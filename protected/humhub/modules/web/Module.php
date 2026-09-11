<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\web;

use humhub\modules\web\security\controllers\ReportController;
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
     * @var mixed web security settings
     */
    public $security;

    /**
     * @inheritdoc
     */
    public $controllerMap = [
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
