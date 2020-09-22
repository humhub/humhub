<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * BaseSidebarItem
 *
 * @since 1.1.1
 * @author Luke
 */
class BaseSidebarItem extends \humhub\components\Widget
{

    /**
     * @var string the title of the sidebar item
     */
    public $title = '';

    /**
     * @var string the default layout for a sidebar item
     */
    public $layout = '@humhub/widgets/views/baseSidebarItem';

    /**
     * @inheritdoc
     */
    public function process()
    {
        $content = $this->run();
        if ($content) {
            return $this->render($this->layout, ['content' => $content, 'title' => $this->title]);
        }
        return;
    }

}
