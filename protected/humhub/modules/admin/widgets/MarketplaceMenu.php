<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2020 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\admin\widgets;

use humhub\widgets\BaseMenu;

/**
 * Marketplace Administration Menu
 */
class MarketplaceMenu extends BaseMenu
{
    public $template = "@humhub/widgets/views/tabMenu";
    public $type = "adminUserSubNavigation";

    public function init()
    {
        parent::init();
    }
}
