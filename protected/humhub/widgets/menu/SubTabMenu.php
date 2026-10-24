<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\menu;

/**
 * SubTabMenu
 *
 * @since 1.4
 * @package humhub\widgets\menu
 */
abstract class SubTabMenu extends TabMenu
{
    /**
     * @var string the title of the panel
     */
    public $panelTitle;

    /**
     * @inheritdoc
     */
    public $template = '@humhub/widgets/menu/views/sub-tab-menu.php';

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'nav nav-tabs tab-sub-menu',
        ];
    }

}
