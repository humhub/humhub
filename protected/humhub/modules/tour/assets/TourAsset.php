<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour\assets;

use humhub\assets\DriverJsAsset;
use humhub\components\View;
use humhub\modules\tour\Module;
use humhub\modules\tour\TourConfig;
use Yii;
use yii\helpers\Url;
use yii\web\AssetBundle;

/**
 * Stream related assets.
 *
 * @since 1.2
 * @author buddha
 */
class TourAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@tour/resources';

    /**
     * @inheritdoc
     */
    public $publishOptions = ['forceCopy' => false];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.tour.min.js',
    ];

    public $css = [
    ];

    public $depends = [
        DriverJsAsset::class,
    ];

    /**
     * @param View $view
     * @return AssetBundle
     */
    public static function register($view)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('tour');

        // Add translatable button labels without overriding configured options.
        // {{current}} and {{total}} are driver.js placeholders and must be preserved.
        // Don't move to Module::init() because Yii::t() would resolve the
        // module's message source and re-instantiate the module (infinite loop).
        $driverJsOptions = $module->driverJsOptions + [
            'nextBtnText' => Yii::t('TourModule.base', 'Next &rarr;'),
            'prevBtnText' => Yii::t('TourModule.base', '&larr; Previous'),
            'doneBtnText' => Yii::t('TourModule.base', 'Done'),
            'progressText' => Yii::t('TourModule.base', '{{current}} of {{total}}'),
        ];

        $view->registerJsConfig('tour', [
            'dashboardUrl' => Url::to(['/dashboard/dashboard']),
            'dashboardTourId' => TourConfig::TOUR_ID_DASHBOARD,
            'completedUrl' => Url::to(['/tour/tour/tour-completed']),
            'driverJsOptions' => $driverJsOptions,
        ]);

        return parent::register($view);
    }
}
