<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour\assets;

use humhub\assets\DriverJsAsset;
use humhub\components\View;
use humhub\modules\tour\models\TourParams;
use humhub\modules\tour\Module;
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

        $view->registerJsConfig('tour', [
            'dashboardUrl' => Url::to(['/dashboard/dashboard']),
            'dashboardPage' => TourParams::PAGE_DASHBOARD,
            'completedUrl' => Url::to(['/tour/tour/tour-completed']),
            'driverOptions' => $module->driverOptions,
        ]);

        return parent::register($view);
    }
}
