<?php


namespace humhub\modules\space\widgets;

class CustomSidebar extends Sidebar
{
    /**
     * @var \humhub\modules\space\models\Space the space this sidebar is in
     */
    public $space;

    /**
     * @var bool
     */
    public $baseWidgets = false;

    /**
     * @var array
     */
    public $customWidgets;

    public function init()
    {
        parent::init();

        if (!$this->baseWidgets)
            $this->widgets = [];

        foreach ($this->customWidgets as $widget) {
            $this->widgets[] = $widget;
        }
    }
}
