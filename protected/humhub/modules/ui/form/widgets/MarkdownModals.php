<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\form\widgets;

use humhub\components\Widget;
use humhub\widgets\LayoutAddons;

/**
 * Class MarkdownModals provides modals which are added used by the Markdown widget.
 * The widget is automatically added to the layout addons.
 *
 * @see LayoutAddons
 * @since 1.3
 */
class MarkdownModals extends Widget
{
    public function run()
    {
        return $this->render('markdownModals');
    }

}
