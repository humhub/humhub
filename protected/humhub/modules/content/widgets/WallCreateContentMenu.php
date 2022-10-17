<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\widgets\stream\WallStreamEntryWidget;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\Menu;
use Yii;

/**
 * WallCreateContentMenu is the widget for Menu above wall create content Form
 *
 * @property-read ContentContainerActiveRecord $contentContainer
 * @property-read bool $isVisible
 * @author luke
 * @since 1.13
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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initEntries();
    }

    private function initEntries()
    {
        if (!empty($this->entries)) {
            return;
        }

        if (!($this->contentContainer instanceof ContentContainerActiveRecord)) {
            return;
        }

        foreach (Yii::$app->moduleManager->getContentClasses($this->contentContainer) as $contentClass) {
            $content = new $contentClass($this->contentContainer);
            if (!($content instanceof ContentActiveRecord)) {
                continue;
            }

            $wallEntryWidget = WallStreamEntryWidget::getByContent($content);
            if (!$wallEntryWidget) {
                continue;
            }

            $menuOptions = [
                'label' => $content->getContentName(),
                'icon' => $content->getIcon(),
                'url' => '#',
                'sortOrder' => $wallEntryWidget->createFormSortOrder ?? '9999999-' . $content->getContentName(),
            ];
            $url = $this->contentContainer->createUrl($wallEntryWidget->createRoute);

            switch ($wallEntryWidget->editMode) {
                case WallStreamEntryWidget::EDIT_MODE_INLINE:
                    $menuOptions['htmlOptions'] = [
                        'data-action-click' => $wallEntryWidget->createFormMenuAction ?? 'loadForm',
                        'data-action-url' => $url,
                    ];
                    break;
                case WallStreamEntryWidget::EDIT_MODE_MODAL:
                    $menuOptions['htmlOptions'] = [
                        'data-action-click' => $wallEntryWidget->createFormMenuAction ?? 'ui.modal.load',
                        'data-action-url' => $url,
                    ];
                    break;
                case WallStreamEntryWidget::EDIT_MODE_NEW_WINDOW:
                    $menuOptions['url'] = $url;
                    break;
                default:
                    $menuOptions = false;
                    break;
            }

            if ($menuOptions) {
                $this->addEntry(new MenuLink($menuOptions));
            }
        }
    }

    public function getIsVisible(): bool
    {
        if ($this->form instanceof WallCreateContentForm &&
            get_class($this->form) !== WallCreateContentForm::class &&
            !$this->form->displayContentTabs) {
            return false;
        }

        $this->initEntries();
        $countEntries = count($this->entries);
        $hasEntryWithForm = self::canCreateEntry($this->contentContainer, 'form');;

        if ($hasEntryWithForm && $countEntries > 1) {
            return true;
        }

        if (!$hasEntryWithForm && $countEntries > 0) {
            return true;
        }

        return false;
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

    /**
     * Check if current User has a permission to create at least one entry on wall stream
     *
     * @param ContentContainerActiveRecord|null $contentContainer
     * @param string $type 'any', 'form' - Entries which are created from Form, 'noform' - Entries which are created without form(like modal window or new page)
     * @return bool
     */
    public static function canCreateEntry(?ContentContainerActiveRecord $contentContainer, string $type = 'any'): bool
    {
        if (!($contentContainer instanceof ContentContainerActiveRecord)) {
            return false;
        }

        foreach (Yii::$app->moduleManager->getContentClasses($contentContainer) as $contentClass) {
            $content = new $contentClass($contentContainer);
            if (!($content instanceof ContentActiveRecord)) {
                continue;
            }

            $wallEntryWidget = WallStreamEntryWidget::getByContent($content);
            if (!$wallEntryWidget) {
                continue;
            }

            switch ($type) {
                case 'any':
                    return true;
                case 'form':
                    if ($wallEntryWidget->hasCreateForm()) {
                        return true;
                    }
                    break;
                case 'noform':
                    if (!$wallEntryWidget->hasCreateForm()) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }
}