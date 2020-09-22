<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\filter\widgets;

use humhub\components\Widget;

/**
 * Widget for rendering a single filter.
 * @since 1.3
 */
class FilterInput extends Widget
{
    /**
     * @var array input options
     */
    public $options = [];

    /**
     * @var string filter id
     */
    public $id;

    /**
     * @var string filter title
     */
    public $title;

    /**
     * @var mixed input value
     */
    public $value;

    /**
     * @var int sort order definition
     */
    public $sortOrder;

    /**
     * @var string css class used for this filter
     */
    public $filterClass = 'filterInput';

    /**
     * @var string defines the filter category, which is used as the key for filter requests as data e.g. {filters = [filter_id_x, filter_id_y]}
     */
    public $category = 'filters';

    /**
     * @var bool defines if the filter category can consist of multiple filter values from different filter inputs
     */
    public $multiple = false;

    /**
     * @var string sets an identifier for this input type and is added as data-filter-type
     */
    public $type;

    /**
     * @var string view to render the input
     */
    public $view;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->prepareOptions();

        return $this->render($this->view, $this->getWidgetOptions());
    }

    protected function prepareOptions()
    {
        $this->options['data-filter-id'] = $this->id;
        $this->options['data-filter-type'] = $this->type;
        $this->options['data-filter-category'] = $this->category;
        $this->options['class'] = $this->filterClass;

        if($this->multiple) {
            $this->options['data-filter-multiple'] = 1;
        }
    }

    protected function getWidgetOptions()
    {
        return [
            'options' => $this->options,
            'title' => $this->title,
            'value' => $this->value
        ];
    }
}
