<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\Menu;
use Yii;

/**
 * WallCreateContentMenu is the widget for Menu above wall create content Form
 * @author luke
 * @since 1.13.0
 */
class WallCreateContentMenu extends Menu
{
    /**
     * @inheritdoc
     */
    public $id = 'contentFormMenu';

    /**
     * @inheritdoc
     */
    public $template = 'wallCreateContentMenu';

    /**
     * @var int What first menu entries should be visible as tabs top level, rest entries will be grouped into sub-menu
     */
    public $visibleEntriesNum = 2;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // TODO: Remove after implement in modules
        $this->initTempTestModulesEntries();

        parent::init();
    }

    private function initTempTestModulesEntries()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('ContentModule.base', 'Poll'),
            'url' => '#',
            'sortOrder' => 200,
            'icon' => 'bar-chart',
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('ContentModule.base', 'Tasks'),
            'url' => '#',
            'sortOrder' => 300,
            'icon' => 'tasks',
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('ContentModule.base', 'Wiki'),
            'url' => '#',
            'sortOrder' => 400,
            'icon' => 'book',
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('ContentModule.base', 'Calendar'),
            'url' => '#',
            'sortOrder' => 400,
            'icon' => 'calendar',
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('ContentModule.base', 'Meetings'),
            'url' => '#',
            'sortOrder' => 400,
            'icon' => 'calendar-o',
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('ContentModule.base', 'News'),
            'url' => '#',
            'sortOrder' => 400,
            'icon' => 'newspaper-o',
        ]));
    }

    /**
     * @return string
     */
    public function run()
    {
        if (count($this->entries) < 2) {
            return '';
        }

        return parent::run();
    }
}