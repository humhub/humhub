<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Yii;
use humhub\modules\user\components\User;

/**
 * TopMenuWidget is the primary top navigation class extended from MenuWidget.
 *
 * @since 0.5
 * @author Luke
 */
class TopMenu extends BaseMenu
{

    /**
     * @inheritdoc
     */
    public $template = 'topNavigation';

    /**
     * @inheritdoc
     */
    public $id = 'top-menu-nav';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

}
