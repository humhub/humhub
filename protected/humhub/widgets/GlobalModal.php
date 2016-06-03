<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * GlobalModal is the standard modal which can be used in every layout.
 * This widget is automatically added to the page via the LayoutAddons.
 *
 * @see LayoutAddons
 * @author Luke
 * @since 1.1
 */
class GlobalModal extends \yii\base\Widget
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('globalModal');
    }

}
