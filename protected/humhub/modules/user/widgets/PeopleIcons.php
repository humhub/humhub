<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\components\Widget;
use humhub\modules\user\models\User;

/**
 * PeopleIcons shows footer icons for people cards
 *
 * @since 1.17.2
 * @author Luke
 */
class PeopleIcons extends Widget
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string $separator Separator between icons
     */
    public string $separator = ' ';

    /**
     * @var array $icons An icon can be HTML code or object convertable to string
     */
    protected array $icons = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        return implode($this->separator, $this->icons);
    }

    /**
     * Add an icon to this widget
     *
     * @param mixed $icon HTML code or object convertable to string
     * @return void
     */
    public function addIcon($icon): void
    {
        $this->icons[] = $icon;
    }
}
