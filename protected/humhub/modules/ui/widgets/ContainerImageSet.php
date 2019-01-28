<?php

namespace humhub\modules\ui\widgets;

class ContainerImageSet extends BaseImage
{
    public $items;

    /**
     * @var int maximal amount of visible items to render
     */
    public $max = 5;

    /**
     * @var int the width of the hidden image
     */
    public $hiddenImageWidth = 50;

    /**
     * @var int the height of the hidden image
     */
    public $hiddenImageHeight = null;

    public function init()
    {
        parent::init();

        if ($this->hiddenImageWidth === null) {
            $this->hiddenImageHeight = $this->hiddenImageWidth;
        }
    }

    public function run()
    {
        $visibleItems = array_slice($this->items, 0, $this->max);
        $hiddenItems = array_slice($this->items, $this->max);
        return $this->render('@ui/widgets/views/containerImageSet', [
            'visibleItems' => $visibleItems,
            'hiddenItems' => $hiddenItems,
            'max' => $this->max,
            'options' => $this->getAvailableOptions(),
            'hiddenItemsOptions' => $this->getOptionsForHiddenItems()
        ]);
    }

    private function getAvailableOptions()
    {
        $excludedParams = ['items', 'max', 'tooltipText', 'hiddenImageWidth', 'hiddenImageHeight'];
        return array_filter(get_object_vars($this), function ($key) use ($excludedParams) {
            return ! in_array($key, $excludedParams);
        }, ARRAY_FILTER_USE_KEY);
    }

    private function getOptionsForHiddenItems()
    {
        return [
            'width' => $this->hiddenImageWidth,
            'height' => $this->hiddenImageHeight
        ];
    }
}