<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\space\models\Space;
use humhub\widgets\BaseSidebar;

/**
 * Sidebar implements the default space sidebar.
 *
 * @author Luke
 * @since 0.5
 */
class Sidebar extends BaseSidebar
{
    /**
     * @var Space the space this sidebar is in
     */
    public $space;

}
