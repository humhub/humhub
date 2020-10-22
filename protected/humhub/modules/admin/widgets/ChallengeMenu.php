<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\admin\widgets;

use humhub\widgets\BaseMenu;

/**
 * Challenge Administration Menu
 */
class ChallengeMenu extends BaseMenu
{
    public $template = "@humhub/widgets/views/tabMenu";
    public $type = "adminUserSubNavigation";

    public function init()
    {
        parent::init();
    }
}
