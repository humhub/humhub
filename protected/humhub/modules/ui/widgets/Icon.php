<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\widgets;

use humhub\components\Widget;

/**
 * Class Icon
 *
 * Wrapper class for icon handling.
 * Currently this class only supports FontAwesome 4.7 icons
 *
 * @since 1.4
 * @package humhub\modules\ui\widgets
 */
class Icon extends Widget
{
    /**
     * @var string the name/id of the icon
     */
    public $name;


    /**
     * @return string returns the Html tag for the current icon
     */
    public function renderHtml()
    {
        return '<i class="fa fa-' . $this->name . '"></i>';
    }


    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->renderHtml();
    }

    /**
     * @return string returns the Html tag for this icon
     */
    public function __toString()
    {
        return $this->renderHtml();
    }

}
