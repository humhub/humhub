<?php


namespace humhub\modules\content\widgets\stream;

use Exception;
use humhub\libs\Html;
use humhub\modules\content\widgets\ArchiveLink;
use humhub\modules\content\widgets\DeleteLink;
use humhub\modules\content\widgets\LockCommentsLink;
use humhub\modules\content\widgets\EditLink;
use humhub\modules\content\widgets\MoveContentLink;
use humhub\modules\content\widgets\NotificationSwitchLink;
use humhub\modules\content\widgets\PermaLink;
use humhub\modules\content\widgets\PinLink;
use humhub\modules\content\widgets\VisibilityLink;
use humhub\modules\dashboard\controllers\DashboardController;
use humhub\modules\space\models\Space;
use humhub\modules\ui\menu\DropdownDivider;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image as UserImage;
use Yii;
use yii\helpers\Url;

/**
 * Base widget class used to render stream entries for the wall stream.
 *
 * Subclasses of this abstract widget are usually assigned to a content type class in the [[ContentActiveRecord::$wallEntryClass]]
 * property.
 *
 * By means of the [[WallStreamEntryRenderOptions]] instance in [[static::$renderOptions]] the appearance of the wall entry
 * can be influenced by:
 *
 * ### Disabling specific addons:
 *
 * ```php
 * public function init()
 * {
 *     parent::init();
 *     // Disable all addons
 *     $this->renderOptions->disableAddons();
 * }
 * ```
 *
 * ```php
 * public function init()
 * {
 *     parent::init();
 *     // Disable the file attachment addon
 *     $this->renderOptions->disableAttachmentAddon();
 * }
 * ```
 *
 * ### Disable context menu entries
 *
 * ```php
 * public function init()
 * {
 *     parent::init();
 *     // Disable the whole context menu
 *     $this->renderOptions->disableContextMenu();
 * }
 * ```
 *
 * ```php
 * public function init()
 * {
 *     parent::init();
 *      // Disable all default context menu entries
 *     $this->renderOptions
 *       ->disableContextDelete()
 *       ->disableContextEdit()
 *       ->disableContextPermalink()
 *       ->disableContextTopics()
 *       ->disableContextSwitchVisibility()
 *       ->disableContextSwitchNotification()
 *       ->disableContextMove()
 *       ->disableContextMenu();
 * }
 * ```
 * @package humhub\modules\content\widgets\stream
 * @since 1.7
 * @see WallStreamEntryOptions
 */
abstract class WallStreamEntryWidget extends StreamEntryWidget
{
    /**
     * Edit form is loaded to the wallentry itself.
     */
    const EDIT_MODE_INLINE = 'inline';

    /**
     * Opens the edit page in a new window.
     */
    const EDIT_MODE_NEW_WINDOW = 'new_window';

    /**
     * Edit form is loaded into a modal.
     */
    const EDIT_MODE_MODAL = 'modal';


    /**
     * Route to edit the content
     *
     * @var string
     */
    public $editRoute;

    /**
     * Defines the way the edit of this wallentry is displayed.
     *
     * @var string
     */
    public $editMode = self::EDIT_MODE_INLINE;

    /**
     * @var string defines the view used to render the entry body layout
     */
    public $layoutBody = '@content/widgets/stream/views/wallStreamEntryBodyLayout';

    /**
     * @var string defines the view used to render the entry header
     */
    public $layoutHeader = '@content/widgets/stream/views/wallStreamEntryHeader';

    /**
     * @var string defines the view used to render the entry footer
     */
    public $layoutFooter = '@content/widgets/stream/views/wallStreamEntryFooter';

    /**
     * @var WallStreamEntryOptions
     */
    public $renderOptions;

    /**
     * @inheritDoc
     */
    protected $renderOptionClass = WallStreamEntryOptions::class;

    /**
     * @return string returns the content type specific part of this wall entry (e.g. post content)
     */
    abstract protected function renderContent();

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        if (!$this->renderOptions) {
            $this->renderOptions = (new WallStreamEntryOptions);
        }

        if ($this->renderOptions->isViewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH) || $this->model->content->isArchived()) {
            // Disable all except permalink
            $this->renderOptions
                ->disableControlsEntryEdit()
                ->disableControlsEntryPin()
                ->disableControlsEntryTopics()
                ->disableControlsEntrySwitchVisibility()
                ->disableControlsEntrySwitchNotification()
                ->disableControlsEntryMove()
                ->disableControlsEntry(DropdownDivider::class);
        }

        if($this->renderOptions->isViewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH)) {
            $this->renderOptions->disableControlsEntryDelete();
        }

        if($this->model->content->container instanceof User && !$this->renderOptions->isViewContext(WallStreamEntryOptions::VIEW_CONTEXT_DEFAULT)) {
            $this->renderOptions->enableContainerInformationInTitle();
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function renderBody()
    {
        return $this->render($this->layoutBody, [
            'model' => $this->model,
            'renderOptions' => $this->renderOptions,
            'content' => $this->renderContent(),
            'header' => $this->renderHeader(),
            'footer' => $this->renderFooter()
        ]);
    }

    /**
     * @return string renders the header section of this wall entry with context menu
     * @throws Exception
     */
    protected function renderHeader()
    {
        return $this->render($this->layoutHeader, [
            'model' => $this->model,
            'renderOptions' => $this->renderOptions,
            'headImage' => $this->renderHeadImage(),
            'title' => $this->renderTitle(),
            'permaLink' => $this->getPermaLink()
        ]);
    }

    /**
     * @return string the title part of this wall entry used in the header section. Note, the return value will NOT be encoded.
     * Therefore you can pass in HTML as links. By default a container link to the author with the displayName of the author
     * is returned.
     */
    protected function renderTitle()
    {
        return Html::containerLink($this->model->content->createdBy);
    }

    /**
     * @return string the permalink of this content model
     */
    protected function getPermaLink()
    {
        return Url::to(['/content/perma', 'id' => $this->model->content->id]);
    }

    /**
     * @return string renders the title image of the header section
     * @throws Exception
     */
    protected function renderHeadImage()
    {
        return $this->renderAuthorHeadImage();
    }

    /**
     * @return string renders the author image for the header section
     * @throws Exception
     */
    protected function renderAuthorHeadImage()
    {
        return UserImage::widget([
            'user' => $this->model->content->createdBy,
            'width' => 40,
            'htmlOptions' => ['class' => 'pull-left']
        ]);
    }


    /**
     * @return string renders the footer section with wall entry addons
     */
    protected function renderFooter()
    {
        return $this->render($this->layoutFooter, [
            'model' => $this->model,
            'renderOptions' => $this->renderOptions
        ]);
    }

    /**
     * Returns an array of context menu items:
     *
     * Add additional entries:
     *
     * ```php
     * $result = parent::getControlsMenuEntries();
     *
     * // Add menu entry by instance (recommended)
     * $result[] = new MySpecialMenuEntry(['model' => $this->model, 'sortOrder'  => 210]);
     *
     * // Add by widget class and definition (deprecated)
     * $result[] = [MyWidget::class, ['model' => $this->model], ['sortOrder' => 210]];
     * ```
     *
     * Sometimes you want to provide a custom menu item for e.g. edit or delete.
     * This can be achieved by:
     *
     * ```php
     * $this->renderOptions->disableContextEdit()->disableContextDelete();
     * $result = parent::getContextMenu();
     * $result[] = new MySpecialEditLink(['model' => $this->model, 'sortOrder'  => 100]);
     * $result[] = new MySpecialDeleteLink(['model' => $this->model, 'sortOrder'  => 300]);
     * ```
     * If an [[editRoute]] is set this function will include an edit button.
     * The edit logic can be changed by changing the [[editMode]].
     *
     * @return array
     * @since 1.2
     */
    public function getControlsMenuEntries()
    {
        if($this->renderOptions->isViewContext([WallStreamEntryOptions::VIEW_CONTEXT_SEARCH])) {
            return [
                [PermaLink::class, ['content' => $this->model], ['sortOrder' => 200]]
            ];
        }

        $result = [
            [PermaLink::class, ['content' => $this->model], ['sortOrder' => 200]],
            [DeleteLink::class, ['content' => $this->model], ['sortOrder' => 300]],
            new DropdownDivider(['sortOrder' => 350]),
            [VisibilityLink::class, ['contentRecord' => $this->model], ['sortOrder' => 400]],
            [LockCommentsLink::class, ['contentRecord' => $this->model], ['sortOrder' => 450]],
            [NotificationSwitchLink::class, ['content' => $this->model], ['sortOrder' => 500]],
            [MoveContentLink::class, ['model' => $this->model], ['sortOrder' => 700]],
            [ArchiveLink::class, ['content' => $this->model], ['sortOrder' => 800]]
        ];

        if($this->renderOptions->isViewContext([WallStreamEntryOptions::VIEW_CONTEXT_DEFAULT, WallStreamEntryOptions::VIEW_CONTEXT_DETAIL])) {
            $result[] =  [PinLink::class, ['content' => $this->model], ['sortOrder' => 600]];
        }

        if (!empty($this->getEditUrl())) {
            $result[] = [EditLink::class, ['model' => $this->model, 'mode' => $this->editMode, 'url' => $this->getEditUrl()], ['sortOrder' => 100]];
        }

        return $result;
    }

    /**
     * Returns the edit url to edit the content (if supported)
     *
     * @return string|null url
     */
    public function getEditUrl()
    {
        if (empty($this->editRoute) || !$this->model->content || !$this->model->content->container) {
            return null;
        }

        // Don't show edit link, when content container is space and archived
        if ($this->model->content->container instanceof Space && $this->model->content->container->status == Space::STATUS_ARCHIVED) {
            return "";
        }

        $params = ['id' => $this->model->id];

        if (Yii::$app->controller instanceof DashboardController) {
            $params['from'] = StreamEntryOptions::VIEW_CONTEXT_DASHBOARD;
        }

        return $this->model->content->container->createUrl($this->editRoute, $params);
    }

    /**
     * @inheritDoc
     */
    public function getAttributes()
    {
        return [
            'class' => $this->renderOptions->isPinned($this->model) ? 'wall-entry pinned-entry' : 'wall-entry'
        ];
    }
}
