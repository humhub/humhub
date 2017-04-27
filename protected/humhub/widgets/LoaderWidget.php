<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * LoaderWidget adds a loader animation
 *
 * @package humhub.widgets
 * @since 0.20
 * @author Andreas Strobel
 */
class LoaderWidget extends \yii\base\Widget
{

    /**
     * id for DOM element
     *
     * @var string
     */
    public $id = "";

    /**
     * css classes for DOM element
     *
     * @var string
     */
    public $cssClass = "";

    /**
     * defines if the loader is initially shown
     */
    public $show = true;

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {
        return $this->render('loader', [
            'id' => $this->id,
            'cssClass' => $this->cssClass,
            'show' => $this->show
        ]);
    }

}
