<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\menu;


/**
 * Class TabMenu
 *
 * @since 1.4
 * @package humhub\widgets\menu
 */
abstract class TabMenu extends Menu
{
    /**
     * @inheritdoc
     */
    public $template = '@humhub/widgets/menu/views/tab-menu.php';

    /**
     * @var bool whether or not to skip rendering if only one menu link is given
     */
    public $renderSingleTab = false;

    public function render($view, $params = [])
    {
        if (!$this->renderSingleTab && !$this->hasMultipleEntries(MenuLink::class)) {
            return '';
        }

        return parent::render($view, $params);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'tab-menu',
        ];
    }

}
