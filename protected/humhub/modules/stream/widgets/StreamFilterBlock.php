<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\widgets;


use humhub\components\Widget;

class StreamFilterBlock extends Widget
{
    /**
     * @var string block title
     */
    public $title;

    /**
     * @var string[] array of filter ids of this block
     */
    public $block;

    /**
     * @var array of all allowed filters
     */
    public $filters;

    /**
     * @var array html options for container
     */
    public $options = [];

    /**
     * @var array html options for the filter link
     */
    public $linkOptions = [];

    /**
     * @var string|[] will be added as data-filter-radio to automatically unset other filters
     */
    public $radio;

    /**
     * @var bool
     */
    public $filterClass = 'wallFilter';

    /**
     * @var array|string of checked filter
     */
    public $checked = [];


    /**
     * @inheritdoc
     */
    public function run()
    {
        $active = false;
        foreach ($this->block as $filterId) {
            if(isset($this->filters[$filterId])) {
                $active = true;
                break;
            }
        }

        if(!$active) {
            return '';
        }

        $this->linkOptions['class'] = $this->filterClass;
        $this->linkOptions['href'] = '#';

        $reversedRadio = [];
        if(is_array($this->radio)) {
            foreach ($this->radio as $key => $filterIds) {
                foreach ($filterIds as $filterId) {
                    $reversedRadio[$filterId] = $key;
                }
            }
        }

        $this->checked = is_string($this->checked) ? [$this->checked] : $this->checked;

        return $this->render('streamFilterBlock', [
            'filters' => $this->filters,
            'block' => $this->block,
            'title' => $this->title,
            'options' => $this->options,
            'radio' => $this->radio,
            'reversedRadio' => $reversedRadio,
            'linkOptions' => $this->linkOptions,
            'checked' => $this->checked]);
    }

}