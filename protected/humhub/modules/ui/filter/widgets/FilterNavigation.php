<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\filter\widgets;

use humhub\widgets\JsWidget;
use yii\base\InvalidArgumentException;

/**
 * This widget is used to render the filter navigation of a filter component.
 *
 * A FilterNavigation groups filters into panels and blocks whereas a filter block is part of a
 * filter panel and can contain multiple filter.
 *
 * Subclasses should initialize the default filter state within the [[initFilterPanels()]], [[initFilterBlocks()]] and
 * [[initFilters()]] functions.
 *
 * The default view expects the following format for the different components:
 *
 * - [[filterPanels]] - Just holds an array of filter block definitions associated by an panel index. An block definition array
 * has to be compatible with the [[StreamFilterBlock]] widget config.
 *
 * - [[filterBlocks]] - Holds an array of all block definitions containing the related filters with block ids as array keys.
 *
 * - [[filters]] - Holds a flat array of filter definitions. A filter definition has to be compatible with [[StreamFilter]] widget config.
 *
 * @since 1.3
 */
abstract class FilterNavigation extends JsWidget
{

    /**
     * @inheritdoc
     */
    public $jsWidget = 'ui.filter.Filter';

    /**
     * @var array filter panels define separate filter container
     */
    public $filterPanels = [];

    /**
     * @var array filter blocks separate filter categories in order to group filters
     */
    public $filterBlocks = [];

    /**
     * @var string can be set to define a default block to add filters with no specific block relation.
     */
    public $defaultBlock;

    /**
     * @var array list of all active filter definitions
     */
    public $filters = [];

    /**
     * @var string view
     */
    public $view = 'filterNavigation';

    /**
     * @var string can be used to identify the related component e.g. a stream
     */
    public $componentId;

    /**
     * Filter definition can be used to manipulate the default settings of a filter.
     *
     * Filters will only be added either if the definition are empty or the filterId is present in the definitions as in
     * the following examples:
     *
     * ```php
     * // This definition would only render the 'my_filter_id' filter while ignoring other filters
     * ['my_filter_id']
     *
     * // This definition overwrites the title of a filter and also will ignore other filters:
     * ['my_filter_id' => ['title' => 'OtherTitle']]
     *
     * // This is a shortcut definition to overwrite the filter title and as in the previous example ignore other filters
     * ['my_filter_id' => 'OtherTitle]
     * ```
     *
     * @var array the filter definition can be used to manipulate the default settings
     */
    public $definition = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initFilterPanels();
        $this->initFilterBlocks();
        $this->initFilters();
        $this->initDefinitionFilters();
    }

    /**
     * Initialization logic for default filter panels
     */
    protected abstract function initFilterPanels();

    /**
     * Initialization logic for default filter blocks.
     *
     * This function can make use of the [[addFilterBlock()]] to add filter blocks to the previously initialized panels
     */
    protected abstract function initFilterBlocks();

    /**
     * Initialization logic for default filter blocks.
     *
     * This function can make use of the [[addFilter()]] to add filters the previously initialized blocks
     */
    protected abstract function initFilters();


    /**
     * Adds additional filters of the filter definition, which are not already part of the default filter setting.
     */
    public function initDefinitionFilters()
    {
        if (empty($this->definition)) {
            return;
        }

        foreach ($this->definition as $key => $value) {
            if (isset($this->filters[$key])) {
                continue;
            }

            if (is_string($key) && is_string($value)) {
                $this->addFilter(['id' => $key, 'title' => $value]);
            } elseif (is_string($key) && is_array($value)) {
                $value['id'] = $key;
                $this->addFilter($value);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render($this->view, [
            'panels' => $this->filterOutEmptyPanels(),
            'options' => $this->getOptions()
        ]);
    }

    public function getData()
    {
        if ($this->componentId) {
            return [
                'filter-component-id' => $this->componentId
            ];
        }

        return parent::getData();
    }

    public function filterOutEmptyPanels()
    {
        $result = [];
        foreach ($this->filterPanels as $key => $blocks) {
            if (empty($blocks)) {
                continue;
            }

            $hasFilter = false;
            foreach ($blocks as $block) {
                if (!empty($block['filters'])) {
                    $hasFilter = true;
                    break;
                }
            }

            if ($hasFilter) {
                $result[$key] = $blocks;
            }
        }

        return $result;
    }

    /**
     * Adds a new filter block to the given `$panel`.
     *
     * @param $id string filter block id
     * @param $definition
     * @param $panel
     */
    public function addFilterBlock($id, $definition, $panel)
    {
        if (!isset($this->filterPanels[$panel])) {
            return;
        }

        $this->filterBlocks[$id] = $definition;
        $this->filterPanels[$panel][] = &$this->filterBlocks[$id];
    }

    /**
     * Adds a Filter with given `$filterId` and filter definition to the filter block with the given `$blockId`.
     *
     * Note: This function will only add the given filter if it is allowed by the [[definition]] and not already set.
     *
     * @param $filter array filter definition
     * @param $blockId string block id
     */
    public function addFilter($filter, $blockId = null)
    {
        if (!isset($filter['id'])) {
            throw new InvalidArgumentException('Filter without filter id given!');
        }

        if (isset($this->filters[$filter['id']]) || !$this->isAllowedFilter($filter['id'])) {
            return;
        }

        if (!isset($filter['class'])) {
            $filter['class'] = CheckboxListFilterInput::class;
        }

        if (array_key_exists($filter['id'], $this->definition)) {
            $definition = $this->definition[$filter['id']];
            if (is_array($definition)) {
                $filter = array_merge($filter, $definition);
            } else {
                $filter['title'] = $definition;
            }
        }

        if ((!$blockId || !isset($this->filterBlocks[$blockId]))) {
            if (!$this->defaultBlock) {
                return;
            }

            $blockId = $this->defaultBlock;
        }

        if (isset($this->filterBlocks[$blockId])) {
            $this->filterBlocks[$blockId]['filters'] = isset($this->filterBlocks[$blockId]['filters']) ? $this->filterBlocks[$blockId]['filters'] : [];
            $this->filterBlocks[$blockId]['filters'][] = $filter;
        }

        $this->filters[$filter['id']] = $filter;
    }

    /**
     * Checks if the given $filterId is allowed by the definition.
     *
     * @param $filterId
     * @return bool
     */
    public function isAllowedFilter($filterId)
    {
        if (empty($this->definition)) {
            return true;
        }

        return array_key_exists($filterId, $this->definition) || in_array($filterId, $this->definition);
    }
}
