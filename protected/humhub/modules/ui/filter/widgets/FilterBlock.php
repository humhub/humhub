<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\filter\widgets;


use humhub\components\Widget;
use yii\helpers\Html;

/**
 * Widget for rendering stream filter blocks.
 *
 * Stream filter blocks are used to categorize filters.
 *
 * @since 1.3
 */
class FilterBlock extends Widget
{
    /**
     * @var string block title
     */
    public $title;

    /**
     * @var array of active filter definitions
     * @see FilterInput
     */
    public $filters = [];

    /**
     * @var array html options for container
     */
    public $options = [];

    /**
     * @var int sort order
     */
    public $sortOrder;

    /**
     * @var string view to render
     */
    public $view = 'filterListBlock';

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (empty($this->filters)) {
            return '';
        }

        Html::addCssClass($this->options, 'filter-block');

        return $this->render($this->view, [
            'filters' => $this->filters,
            'title' => $this->title,
            'options' => $this->options,
        ]);
    }

    public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }

}
