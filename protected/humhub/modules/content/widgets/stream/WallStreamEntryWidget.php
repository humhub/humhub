<?php


namespace humhub\modules\content\widgets\stream;


use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\content\widgets\ArchiveLink;
use humhub\modules\content\widgets\DeleteLink;
use humhub\modules\content\widgets\EditLink;
use humhub\modules\content\widgets\MoveContentLink;
use humhub\modules\content\widgets\NotificationSwitchLink;
use humhub\modules\content\widgets\PermaLink;
use humhub\modules\content\widgets\PinLink;
use humhub\modules\content\widgets\VisibilityLink;
use humhub\modules\dashboard\controllers\DashboardController;
use humhub\modules\space\models\Space;
use humhub\modules\stream\actions\Stream;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\menu\DropdownDivider;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\user\widgets\Image as UserImage;
use Yii;

/**
 * Class WallStreamEntryWidget
 * @package humhub\modules\content\widgets\stream
 * @since 1.7
 */
abstract class WallStreamEntryWidget extends BaseStreamEntryWidget
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
    public $editRoute = "";

    /**
     * Defines the way the edit of this wallentry is displayed.
     *
     * @var string
     */
    public $editMode = self::EDIT_MODE_INLINE;

    const RENDER_OPTION_ADDONS = 'addons';
    const RENDER_OPTION_CONTROLS = 'controls';
    const RENDER_OPTION_CONTAINERINFO = 'containerInfo';
    const RENDER_OPTION_JUSTEDITED = 'justEdited';

    public $layoutMain = '@content/widgets/stream/views/wallStreamEntryLayout';
    public $layoutHeader =  '@content/widgets/stream/views/wallStreamEntryHeader';
    public $layoutFooter =  '@content/widgets/stream/views/wallStreamEntryFooter';

    abstract protected function renderContent();

    /**
     * @return string
     * @throws \Exception
     */
    protected function renderContentLayout()
    {
        return $this->render($this->layoutMain, [
            'model' => $this->model,
            'content' => $this->renderContent(),
            'header' => $this->renderHeader(),
            'footer' => $this->renderFooter()
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function renderHeader()
    {
        return $this->render($this->layoutHeader, [
            'model' => $this->model,
            'isPinned' => $this->isPinned(),
            'headImage' => $this->renderHeadImage(),
            'showContainerInfo' => $this->isShowContainerInfo(),
            'controlsOptions' =>  $this->getRenderOptions(static::RENDER_OPTION_CONTROLS, [])
        ]);
    }

    private function isShowContainerInfo()
    {
        $container = $this->model->content->container;

        if(!$container) {
            return false;
        }

        if($container->is($this->model->content->createdBy)) {
            return false;
        }

        return $this->getRenderOptions(static::RENDER_OPTION_CONTAINERINFO, !$this->model->content->container->is(ContentContainerHelper::getCurrent()));
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function renderHeadImage()
    {
        return $this->renderIconImage();
        $user = $this->model->content->createdBy;
        $container = $this->model->content->container;

        $result = UserImage::widget([
            'user' => $user,
            'width' => 40,
            'htmlOptions' => ['class' => 'pull-left', 'data-contentcontainer-id' => $user->contentcontainer_id]
        ]);

        return $result;
/*
        if ( $container instanceof Space && $this->isShowContainerInfo()) {
            $result .= SpaceImage::widget([
                'space' => $container,
                'width' => 20,
                'htmlOptions' => ['class' => 'img-space'],
                'link' => 'true',
                'linkOptions' => ['class' => 'pull-left', 'data-contentcontainer-id' => $container->contentcontainer_id],
            ]);
        }

        return $result;*/
    }

    protected function renderIconImage()
    {
        $icons = [
            'calendar',
            'question-circle-o',
            'question-circle',
            'file-o',
            'tasks',
        ];

        return Icon::get($icons[array_rand($icons)], ['fixedWidth' => true])->asString();
    }

    protected function renderFooter()
    {
        // addonOptions can be an array or false
        $addonOptions = $this->getRenderOptions(static::RENDER_OPTION_ADDONS, []);

        if(is_array($addonOptions)) {
            $addonOptions = array_merge($addonOptions, ['object' => $this->model]);
        } else if($addonOptions !== false) {
            $addonOptions = ['object' => $this->model];
        }

        return $this->render($this->layoutFooter, [
            'model' => $this->model,
            'addonOptions' => $addonOptions
        ]);
    }

    /**
     * Returns an array of context menu items either in form of a single array:
     *
     * ['label' => 'mylabel', 'icon' => 'fa-myicon', 'data-action-click' => 'myaction', ...]
     *
     * or as widget type definition:
     *
     * [MyWidget::class, [...], [...]]
     *
     * If an [[editRoute]] is set this function will include an edit button.
     * The edit logic can be changed by changing the [[editMode]].
     *
     * @return array
     * @since 1.2
     */
    public function getContextMenu()
    {
        $result = [];

        if (!empty($this->getEditUrl())) {
            $this->addControl($result, [EditLink::class, ['model' => $this->model, 'mode' => $this->editMode, 'url' => $this->getEditUrl()], ['sortOrder' => 100]]);
        }

        $this->addControl($result, [PermaLink::class, ['content' => $this->model], ['sortOrder' => 200]]);

        $this->addControl($result, [DeleteLink::class, ['content' => $this->model], ['sortOrder' => 300]]);

        $this->addControl($result, new DropdownDivider(['sortOrder' => 350]));
        $this->addControl($result, [VisibilityLink::class, ['contentRecord' => $this->model], ['sortOrder' => 400]]);
        $this->addControl($result, [NotificationSwitchLink::class, ['content' => $this->model], ['sortOrder' => 500]]);
        $this->addControl($result, [PinLink::class, ['content' => $this->model], ['sortOrder' => 600]]);
        $this->addControl($result, [MoveContentLink::class, ['model' => $this->model], ['sortOrder' => 700]]);
        $this->addControl($result, [ArchiveLink::class, ['content' => $this->model], ['sortOrder' => 800]]);

        /*if (isset($this->controlsOptions['add'])) {
            foreach ($this->controlsOptions['add'] as $linkOptions) {
                $this->addControl($result, $linkOptions);
            }
        }*/

        return $result;
    }

    protected function addControl(&$result, $entry)
    {
        $entryClass = null;
        if ($entry instanceof MenuEntry) {
            $entryClass = get_class($entry);
        } elseif (is_array($entry) && isset($entry[0])) {
            $entryClass = $entry[0];
        }

       /* if (isset($this->controlsOptions['prevent']) && $entryClass && in_array($entryClass, $this->controlsOptions['prevent'])) {
            return;
        }*/

        $result[] = $entry;
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
            $params['from'] = Stream::FROM_DASHBOARD;
        }
        return $this->model->content->container->createUrl($this->editRoute, $params);
    }
}
