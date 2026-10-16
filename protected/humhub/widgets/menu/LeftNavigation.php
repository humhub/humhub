<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\menu;

/**
 * Class LeftNavigation
 *
 * @since 1.4
 * @package humhub\widgets\menu
 */
abstract class LeftNavigation extends Menu
{
    /**
     * @var string the title of the panel
     */
    public $panelTitle;

    /**
     * @inheritdoc
     */
    public $template = '@humhub/widgets/menu/views/left-navigation.php';

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'panel panel-default left-navigation',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            'aria-label' => strip_tags($this->panelTitle),
        ]);
    }
}
