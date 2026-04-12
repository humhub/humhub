<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu\widgets;

/**
 * Class DropdownMenu
 *
 * @since 1.4
 * @package humhub\modules\ui\menu\widgets
 */
abstract class DropdownMenu extends Menu
{
    /**
     * @var ?string the label of the dropdown button
     */
    public ?string $label = null;
    /**
     * @var bool whether the label should be HTML-encoded.
     */
    public bool $encodeLabel = true;
    /**
     * @var ?string the icon of the dropdown button
     */
    public ?string $icon = null;

    /**
     * @inheritdoc
     */
    public $template = '@ui/menu/widgets/views/dropdown-menu.php';


    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'btn-group dropdown',
        ];
    }

}
