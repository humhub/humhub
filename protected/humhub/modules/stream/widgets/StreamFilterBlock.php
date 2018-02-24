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
     * @var boolean true if filter is checked
     */
    public $checked = false;

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

        return $this->render('streamFilterBlock', [
            'filters' => $this->filters,
            'block' => $this->block,
            'title' => $this->title,
            'checked' => $this->checked]);
    }

}