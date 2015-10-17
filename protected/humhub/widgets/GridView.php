<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * @inheritdoc
 */
class GridView extends \yii\grid\GridView
{

    const EVENT_INIT = 'init';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->trigger(self::EVENT_INIT);
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public $tableOptions = ['class' => 'table table-hover'];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $loaderJs = '$(document).ready(function () {
                $(".grid-view-loading").show();
                $(".grid-view-loading").css("display", "block !important");
                $(".grid-view-loading").css("opacity", "1 !important");
        });';

        $this->getView()->registerJs($loaderJs);

        return parent::run();
    }

}
