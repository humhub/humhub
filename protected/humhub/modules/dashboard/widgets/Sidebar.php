<?php

namespace humhub\modules\dashboard\widgets;

use humhub\modules\activity\widgets\ActivityBox;
use humhub\modules\dashboard\Module;
use humhub\widgets\BaseSidebar;
use Yii;

class Sidebar extends BaseSidebar
{
    public function init()
    {
        parent::init();

        /** @var Module $module */
        $module = Yii::$app->getModule('dashboard');

        if ($module->hideActivitySidebarWidget) {
            foreach ($this->widgets as $k => $widget) {
                if (isset($widget[0]) && ($widget[0] === ActivityBox::class)) {
                    unset($this->widgets[$k]);
                }
            }
        }
    }
}
