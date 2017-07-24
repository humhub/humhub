<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets;

use humhub\components\Widget;
use humhub\libs\Html;
use Yii;


/**
 * Labels for Wall Entries
 * This widget will attached labels like Pinned, Archived to Wall Entries
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class Label extends BootstrapComponent
{

    public $_sortOrder = 1000;

    public function sortOrder($sortOrder)
    {
        $this->_sortOrder = $sortOrder;
        return $this;
    }

    /**
     * @return string renders and returns the actual html element by means of the current settings
     */
    public function renderComponent()
    {
        return Html::tag('span', $this->getText(), $this->htmlOptions);
    }

    /**
     * @return string the bootstrap css base class
     */
    public function getComponentBaseClass()
    {
        return 'label';
    }

    /**
     * @return string the bootstrap css class by $type
     */
    public function getTypedClass($type)
    {
        return 'label-'.$type;
    }

    public static function sort(&$labels)
    {
        usort($labels, function ($a, $b) {
            if ($a->_sortOrder == $b->_sortOrder) {
                return 0;
            } else
                if ($a->_sortOrder < $b->_sortOrder) {
                    return - 1;
                } else {
                    return 1;
                }
        });

        return $labels;
    }
}

?>