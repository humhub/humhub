<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\filter\widgets;

use humhub\components\Widget;
use humhub\libs\Sort;

/**
 * Renders a single stream filter panel which is part of a [[StreamFilterNavigation]].
 *
 * @since 1.3
 * @see FilterNavigation
 */
class FilterPanel extends Widget
{
    /**
     * @var array stream filter block definitions
     */
    public $blocks = [];

    public $view = 'filterPanel';

    public $span = 3;

    /**
     * @inheritdoc
     */
    public function run()
    {

        if(empty($this->blocks)) {
            return '';
        }

        return $this->render($this->view, ['blocks' => Sort::sort($this->blocks), 'span' => $this->span]);
    }
}
