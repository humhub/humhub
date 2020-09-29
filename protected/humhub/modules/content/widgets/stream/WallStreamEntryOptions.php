<?php


namespace humhub\modules\content\widgets\stream;

use humhub\modules\comment\widgets\Comments;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\content\widgets\ArchiveLink;
use humhub\modules\content\widgets\DeleteLink;
use humhub\modules\content\widgets\EditLink;
use humhub\modules\content\widgets\MoveContentLink;
use humhub\modules\content\widgets\NotificationSwitchLink;
use humhub\modules\content\widgets\PermaLink;
use humhub\modules\content\widgets\PinLink;
use humhub\modules\content\widgets\VisibilityLink;
use humhub\modules\content\widgets\WallEntryLinks;
use humhub\modules\file\widgets\ShowFiles;
use humhub\modules\space\models\Space;
use humhub\modules\topic\widgets\ContentTopicButton;
use humhub\modules\ui\menu\MenuEntry;
use yii\base\Model;

/**
 * This option class can be used to influence the rendering of a wall entry.
 *
 * Usage examples:
 *
 * ```
 * // Disable all wall wall entry addons
 * (new WallStreamEntryOptions)->disableAddons();
 *
 * // Disables stream attachments
 * (new WallStreamEntryOptions)->disableAttachmentAddon();
 *
 * // Disable wall entry links (Comment/Like)
 * (new WallStreamEntryOptions)->disableWallEntryLinks();
 *
 * ```
 * @package humhub\modules\content\widgets\stream
 * @since 1.7
 */
class WallStreamEntryOptions extends StreamEntryOptions
{
    /**
     * Used when rendering the entry on the dashboard
     */
    const VIEW_CONTEXT_SEARCH = 'search';

    /**
     * @var array contains option settings for wall entry addons widgets, e.g. used to disable widgets
     */
    private $addonOptions = [];

    /**
     * @var array contains options settings for the wall entry controls menu, e.g. used to disable certain menu items
     */
    private $controlsMenuOptions = [];

    /**
     * @var bool If true, all wall entry addons are disabled
     */
    public $disableAddons = false;

    /**
     * @var bool Flag indicating whether or not this content was just edited
     */
    public $justEdited = false;

    /**
     * @var bool if true, the whole wall entry controls will not be rendered
     */
    public $disableControlsMenu = false;

    /**
     * @var bool whether or not the author name should be rendered in the sub headline, other conditions nee
     */
    public $enableSubHeadlineAuthor = false;

    /**
     * @var bool whether or not the container information should be rendered in the main title
     */
    public $enableContainerInformationInTitle = false;

    /**
     * Defines if the sub headline author information should be enabled
     * @param bool $val
     * @return $this
     */
    public function enableSubHeadlineAuthor($val = true)
    {
        $this->enableSubHeadlineAuthor = $val;
        return $this;
    }

    /**
     * Checks if the author information should be rendered in the sub headline for the given $model
     * @param ContentActiveRecord $model
     * @return bool
     */
    public function isShowAuthorInformationInSubHeadLine(ContentActiveRecord $model)
    {
        return $this->enableSubHeadlineAuthor;
    }

    /**
     * Defines if the content was just edited or not.
     * @param bool $val
     * @return $this
     */
    public function justEdited($val = true)
    {
        $this->justEdited = $val;
        return $this;
    }

    /**
     * @return bool checks if the content was just edited or not.
     */
    public function isJustEdited()
    {
        return $this->justEdited;
    }

    /**
     * Defines if the controls menu should be disabled or not.
     * @return $this
     */
    public function disableControlsMenu($val = true)
    {
        $this->disableControlsMenu = $val;
        return $this;
    }

    /**
     * @return bool checks if the controls menu was disabled.
     */
    public function isControlsMenuDisabled()
    {
        return $this->disableControlsMenu;
    }

    /**
     * Disables the controls menu edit entry
     * @return WallStreamEntryOptions
     */
    public function disableControlsEntryEdit()
    {
        return $this->disableControlsEntry(EditLink::class);
    }

    /**
     * Disables the controls menu topic entry
     * @return WallStreamEntryOptions
     */
    public function disableControlsEntryTopics()
    {
        return $this->disableControlsEntry(ContentTopicButton::class);
    }

    /**
     * Disables the controls menu permalink entry
     * @return WallStreamEntryOptions
     */
    public function disableControlsEntryPermalink()
    {
        return $this->disableControlsEntry(PermaLink::class);
    }

    /**
     * Disables the controls menu delete entry
     * @return WallStreamEntryOptions
     */
    public function disableControlsEntryDelete()
    {
        return $this->disableControlsEntry(DeleteLink::class);
    }

    /**
     * Disables the controls menu visibility switch
     * @return WallStreamEntryOptions
     */
    public function disableControlsEntrySwitchVisibility()
    {
        return $this->disableControlsEntry(VisibilityLink::class);
    }

    /**
     * Disables the controls menu notification switch
     * @return WallStreamEntryOptions
     */
    public function disableControlsEntrySwitchNotification()
    {
        return $this->disableControlsEntry(NotificationSwitchLink::class);
    }

    /**
     * Disables the controls menu pin entry
     * @return WallStreamEntryOptions
     */
    public function disableControlsEntryPin()
    {
        return $this->disableControlsEntry(PinLink::class);
    }

    /**
     * Disables the controls menu move entry
     * @return WallStreamEntryOptions
     */
    public function disableControlsEntryMove()
    {
        return $this->disableControlsEntry(MoveContentLink::class);
    }

    /**
     * Disables the controls menu archive entry
     * @return WallStreamEntryOptions
     */
    public function disableControlsEntryArchive()
    {
        return $this->disableControlsEntry(ArchiveLink::class);
    }

    /**
     * Disables a given menu entry either by widget class or id.
     * @param $menuEntryIdentity
     * @return $this
     */
    public function disableControlsEntry($menuEntryIdentity)
    {
        $this->controlsMenuOptions[$menuEntryIdentity] = false;
        return $this;
    }

    /**
     * Checks if a given menu entry was disabled
     * @param MenuEntry $entry
     * @return bool
     */
    public function isContextMenuEntryDisabled(MenuEntry $entry)
    {
        return (isset($this->controlsMenuOptions[$entry->getId()]) && $this->controlsMenuOptions[$entry->getId()] === false)
            || (isset($this->controlsMenuOptions[$entry->getEntryClass()]) && $this->controlsMenuOptions[$entry->getEntryClass()] === false);
    }

    /**
     * Defines if the container information in the main title should be rendered
     * @param bool $val
     * @return $this
     */
    public function enableContainerInformationInTitle($val = true)
    {
        $this->enableContainerInformationInTitle = $val;
        return $this;
    }

    /**
     * Checks if the container information in the main title was enabled
     * @param ContentActiveRecord $model
     * @return bool
     */
    public function isShowContainerInformationInTitle(ContentActiveRecord $model)
    {
        return $this->enableContainerInformationInTitle && $this->isShowContainerInformation($model);
    }

    /**
     * Checks if the container information in sub title should be rendered
     * @param ContentActiveRecord $model
     * @return bool
     */
    public function isShowContainerInformationInSubTitle(ContentActiveRecord $model)
    {
        return $this->isShowContainerInformation($model) && !$this->isShowContainerInformationInTitle($model);
    }

    /**
     * Disables all widget addons
     * @return $this
     */
    public function disableAddons()
    {
        $this->disableAddons = true;
        return $this;
    }

    /**
     * @return bool checks if all addons are disabled
     */
    public function isAddonsDisabled()
    {
        return $this->disableAddons;
    }

    /**
     * Disables the given widget addon class
     * @param $widgetClass string class name of the widget
     * @return $this
     */
    public function disableAddonWidget($widgetClass)
    {
        $this->addonOptions[$widgetClass] = false;
        return $this;
    }

    /**
     * @param $widgetClass string class name of the widget
     * @return false|array|null either false to disable a widget addon, or an options array to overwrite widget options
     * or null if no specific options are set
     */
    public function getAddonWidgetOptions($widgetClass)
    {
        return $this->addonOptions[$widgetClass] ?? null;
    }

    /**
     * @return boolean checks if the given addon widget class is disabled
     */
    public function isAddonDisabled($widgetClass)
    {
        return $this->getAddonWidgetOptions($widgetClass) === false;
    }

    /**
     * Disables the comment section
     * @return $this
     */
    public function disableCommentAddon()
    {
        return $this->disableAddonWidget(Comments::class);
    }

    /**
     * Disables all file attachments including file preview
     * @return $this
     */
    public function disableAttachmentAddon()
    {
        return $this->disableAddonWidget(ShowFiles::class);
    }

    /**
     * Disables all wall entry links
     * @return $this
     */
    public function disableWallEntryLinks()
    {
        return $this->disableAddonWidget(WallEntryLinks::class);
    }
}
