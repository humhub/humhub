<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use yii\helpers\Json;
use yii\widgets\PjaxAsset;

/**
 * Pjax Widget
 *
 * @author Luke
 */
class Pjax extends \humhub\components\Widget
{

    /**
     * @var array options passed to pjax scrpit
     */
    public $clientOptions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->clientOptions['pushRedirect'] = true;
        $this->clientOptions['replaceRedirect'] = false;
        $this->clientOptions['cache'] = false;
        $this->clientOptions['timeout'] = 5000;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $view = $this->getView();
        PjaxAsset::register($view);

        $js = 'jQuery(document).pjax("a", "#layout-content", ' . Json::htmlEncode($this->clientOptions) . ');';
        $view->registerJs($js);
    }

}
