<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dashboard\widgets;

use humhub\modules\activity\widgets\ActivityStreamViewer;
use humhub\modules\dashboard\Module;
use humhub\widgets\BaseSidebar;
use Yii;

/**
 * Sidebar implements the dashboards sidebar
 */
class Sidebar extends BaseSidebar
{

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        /** @var Module $module */
        $module = Yii::$app->getModule('dashboard');

        if ($module->hideActivitySidebarWidget) {
            foreach ($this->widgets as $k => $widget) {
                if (isset($widget[0]) && ($widget[0] === ActivityStreamViewer::class || $widget[0] === 'humhub\modules\activity\widgets\Stream')) {
                    unset($this->widgets[$k]);
                }
            }
        }
    }

}
