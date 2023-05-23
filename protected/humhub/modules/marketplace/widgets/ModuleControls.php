<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

/**
 * Widget for rendering the context menu for module.
 */
class ModuleControls extends \humhub\modules\admin\widgets\ModuleControls
{
    /**
     * @inheritdoc
     */
    public $template = '@marketplace/widgets/views/moduleControls';
}
