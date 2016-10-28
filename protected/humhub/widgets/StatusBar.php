<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * StatusBar for user feedback (error/warning/info).
 *
 * @see LayoutAddons
 * @author buddha
 * @since 1.2
 */
class StatusBar extends \yii\base\Widget
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('statusBar');
    }

}
