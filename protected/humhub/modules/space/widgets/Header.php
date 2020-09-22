<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use Yii;
use yii\base\Widget;

/**
 * This widget will added to the sidebar and show infos about the current selected space
 *
 * @author Andreas Strobel
 * @since 0.5
 */
class Header extends Widget
{

    /**
     * @var \humhub\modules\space\models\Space the Space which this header belongs to
     */
    public $space;

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('header', [
            'space' => $this->space,
            // Deprecated variables below (will removed in future versions)
            'followingEnabled' => !Yii::$app->getModule('space')->disableFollow,
            'postCount' => -1
        ]);
    }

}
