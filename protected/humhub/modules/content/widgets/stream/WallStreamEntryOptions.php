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
 */
class WallStreamEntryOptions extends StreamEntryOptions
{
    /**
     * Used when rendering the entry on the dashboard
     */
    const VIEW_CONTEXT_SEARCH = 'search';

    private $addonOptions = [];
    private $disableAddons = false;

    private $justEdited = false;

    public $contextMenuOptions = [];
    private $disableContextMenu = false;

    private $disableSubHeadlineAuthor = false;

    private $disableTargetSpaceImage = true;

    public function disableSubHeadlineAuthor()
    {
        $this->disableSubHeadlineAuthor = true;
    }

    public function disableTargetSpaceImage()
    {
        $this->disableTargetSpaceImage = true;
        return $this;
    }

    public function isShowTargetSpaceImage(ContentActiveRecord $model)
    {
        return !$this->disableTargetSpaceImage
            && $model->content->container instanceof Space
            && $this->isShowContainerInformation($model);
    }

    public function isShowAuthorLinkInSubHeadLine(ContentActiveRecord $model)
    {
        !$this->disableSubHeadlineAuthor && !$model->content->container->is($model->content->createdBy);
    }

    public function isShowContainerInformation(ContentActiveRecord $model)
    {
        if(!$model->content->container) {
            return false;
        }

        if($model->content->container->is($model->content->createdBy)) {
            return false;
        }

        if(!ContentContainerHelper::getCurrent()) {
            return true;
        }

        return !$this->isViewMode(static::VIEW_CONTEXT_CONTAINER);
    }

    public function setJustEdited($val = true)
    {
        $this->justEdited = $val;
    }

    public function isJustEdited()
    {
        return $this->justEdited;
    }

    public function disableContextMenu()
    {
        $this->disableContextMenu = true;
        return $this;
    }

    public function isContextMenuDisabled()
    {
        return $this->disableContextMenu;
    }

    public function disableContextEdit()
    {
        return $this->disableContextMenuEntry(EditLink::class);
    }

    public function disableContextTopics()
    {
        return $this->disableContextMenuEntry(ContentTopicButton::class);
    }

    public function disableContextPermalink()
    {
        return $this->disableContextMenuEntry(PermaLink::class);
    }

    public function disableContextDelete()
    {
        return $this->disableContextMenuEntry(DeleteLink::class);
    }

    public function disableContextSwitchVisibility()
    {
        return $this->disableContextMenuEntry(VisibilityLink::class);
    }

    public function disableContextSwitchNotification()
    {
        return $this->disableContextMenuEntry(NotificationSwitchLink::class);
    }

    public function disableContextPin()
    {
        return $this->disableContextMenuEntry(PinLink::class);
    }

    public function disableContextMove()
    {
        return $this->disableContextMenuEntry(MoveContentLink::class);
    }

    public function disableContextArchive()
    {
        return $this->disableContextMenuEntry(ArchiveLink::class);
    }

    public function isContextMenuEntryDisabled(MenuEntry $entry)
    {
        return (isset($this->contextMenuOptions[$entry->getId()]) && $this->contextMenuOptions[$entry->getId()] === false)
            || (isset($this->contextMenuOptions[$entry->getEntryClass()]) && $this->contextMenuOptions[$entry->getEntryClass()] === false);
    }

    public function disableContextMenuEntry($menuEntryIdentity)
    {
        $this->contextMenuOptions[$menuEntryIdentity] = false;
        return $this;
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
