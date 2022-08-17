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
    public $jsWidget = 'content.form.CreateFormMenu';

    /**
     * @inheritdoc
     */
    public $init = true;

    /**
     * @inheritdoc
     */
    public $template = 'wallCreateContentMenu';

    /**
     * @var int What first menu entries should be visible as tabs top level, rest entries will be grouped into sub-menu
     */
    public $visibleEntriesNum = 2;

    /**
     * @var WallCreateContentForm|null
     */
    public $form;

    /**
     * @var bool Visible by default depending on property `$this->form->displayMenu`
     */
    public $isVisible;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->isVisible === null) {
            $this->isVisible = ($this->form instanceof WallCreateContentForm) && $this->form->displayMenu;
        }

        if (!$this->isVisible) {
            return;
        }

        // TODO: Remove after implement in modules
        $this->initTempTestModulesEntries();

        // Make this widget visible only when two and more entries
        $this->isVisible = count($this->entries) > 1;
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
     * @inheritdoc
     */
    public function run()
    {
        return $this->isVisible ? parent::run() : '';
    }
}