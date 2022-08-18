<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\ui\menu\widgets\Menu;

/**
 * WallCreateContentMenu is the widget for Menu above wall create content Form
 *
 * @property-read ContentContainerActiveRecord $contentContainer
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

        // Make this widget visible only when two and more entries
        $this->isVisible = count($this->entries) > 1;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->isVisible ? parent::run() : '';
    }

    public function getContentContainer(): ?ContentContainerActiveRecord
    {
        return isset($this->form, $this->form->contentContainer) &&
            $this->form instanceof WallCreateContentForm &&
            $this->form->contentContainer instanceof ContentContainerActiveRecord
            ? $this->form->contentContainer
            : null;
    }
}