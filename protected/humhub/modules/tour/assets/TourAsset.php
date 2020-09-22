<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour\assets;

use Yii;
use yii\helpers\Url;
use yii\web\AssetBundle;
use humhub\modules\ui\view\components\View;

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
        'js/bootstrap-tourist.min.js',
        'js/humhub.tour.js'
    ];

    public $css = [
        'css/bootstrap-tourist.min.css'
    ];

    /**
     * @param View $view
     * @return AssetBundle
     */
    public static function register($view)
    {
        $view->registerJsConfig('tour', [
            'dashboardUrl' => Url::to(['/dashboard/dashboard']),
            'completedUrl' => Url::to(['/tour/tour/tour-completed']),
            'template' => '<div class="popover tour" role="tooltip"> <div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-default" data-role="prev">'.Yii::t('TourModule.base', '« Prev').'</button> <button class="btn btn-sm btn-default" data-role="next">'.Yii::t('TourModule.base', 'Next »').'</button> <button class="btn btn-sm btn-default" data-role="pause-resume" data-pause-text="Pause" data-resume-text="Resume">Pause</button> </div> <button class="btn btn-sm btn-default" data-role="end">'.Yii::t('TourModule.base', 'End guide').'</button> </div> </div>'
        ]);
        return parent::register($view);
    }
}

