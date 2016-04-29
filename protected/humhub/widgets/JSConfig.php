<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use yii\base\Widget;

/**
 * AjaxButton is an replacement for Yii1 CHtml::AjaxButton
 *
 * @author luke
 */
class JSConfig extends Widget
{
    public function run()
    {
        return $this->render('jsConfig');
    }

}
