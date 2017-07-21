<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use Yii;
use humhub\components\Widget;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\content\components\ContentContainerController;

/**
 * WallEntry is responsible to show a content inside a stream/wall.
 * 
 * @see \humhub\modules\content\components\ContentActiveRecord
 * @since 0.20
 * @author luke
 */
class WallEntry extends Widget
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
     * The content object
     *
     * @var \humhub\modules\content\components\ContentActiveRecord
     */
    public $contentObject;

    /**
     * Indicates the post was just edited
     *
     * @var boolean
     */
    public $justEdited = false;

    /**
     * Route to edit the content
     * 
     * @var string
     */
    public $editRoute = "";

    /**
     * Defines the way the edit of this wallentry is displayed.
     * 
     * @var type 
     */
    public $editMode = self::EDIT_MODE_INLINE;

    /**
     * The wall entry layout to use
     * 
     * @var string
     */
    public $wallEntryLayout = "@humhub/modules/content/widgets/views/wallEntry.php";

    /**
     * @var string Defines the wallEntry Javascript implementation.
     */
    public $jsWidget = "stream.StreamEntry";

    /**
     * Can be used to overwrite default options of WallEntryAddon Widgets as for example ShowFiles etc.
     *
     * e.g. in order to disable file preview of files:
     *
     * ```php
     * public $addonOptions = [
     *   ShowFiles::class => [
     *      'renderPreview' => false
     *   ]
     *];
     * ```
     * @var array
     */
    public $addonOptions = [];

    /**
     * Can be used to prevent the rendering of certain WallEntryControl links
     *
     * e.g. in order to prevent the ArchiveLink to be rendered:
     *
     * public $controlsOptions = [
     *     'prevent' =>  [
     *       ArchiveLink::class
     *     ]
     * ];
     *
     * @var array
     */
    public $controlsOptions = [];

    /**
     * @deprecated since 1.2 use $addonOptions of ShowFiles Widget or disable 'show_in_stream' file flag.
     * @var boolean show files widget containing a list of all assigned files
     */
    public $showFiles = true;

    /**
     * @var boolean if set to false, the WallEntryControls won't be rendered
     */
    public $renderControls = true;

    /**
     * @var boolean if set to false, the WallEntryAddons won't be rendered (like/comments/etc)
     */
    public $renderAddons = true;

    /**
     * @inheritdoc
     */
    public static function widget($config = [])
    {
        ob_start();
        ob_implicit_flush(false);
        try {
            /* @var $widget Widget */
            $config['class'] = get_called_class();
            $widget = Yii::createObject($config);
            $out = $widget->render($widget->wallEntryLayout, $widget->getWallEntryViewParams());
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean() . $out;
    }

    /**
     * Returns the edit url to edit the content (if supported)
     * 
     * @return string url
     */
    public function getEditUrl()
    {
        if (empty($this->editRoute)) {
            return;
        }

        // Don't show edit link, when content container is space and archived
        if ($this->contentObject->content->container instanceof Space && $this->contentObject->content->container->status == Space::STATUS_ARCHIVED) {
            return "";
        }

        return $this->contentObject->content->container->createUrl($this->editRoute, ['id' => $this->contentObject->id]);
    }

    /**
     * Returns an array of contextmenu items either in form of a single array:
     * 
     * ['label' => 'mylabel', icon => 'fa-myicon', 'data-action-click' => 'myaction', ...]
     * 
     * or as widget type definition:
     * 
     * [MyWidget::class, [...], [...]]
     * 
     * If an $editRoute is set this function will include an edit button.
     * The edit logic can be changed by changing the $editMode.
     * 
     * @return array
     * @since 1.2
     */
    public function getContextMenu()
    {
        $result = [];
        if (!empty($this->getEditUrl())) {
            $this->addControl($result, [EditLink::class, ['model' => $this->contentObject, 'mode' => $this->editMode, 'url' => $this->getEditUrl()], ['sortOrder' => 200]]);
        }

        $this->addControl($result, [DeleteLink::class, ['content' => $this->contentObject], ['sortOrder' => 100]]);
        $this->addControl($result, [VisibilityLink::class, ['contentRecord' => $this->contentObject], ['sortOrder' => 250]]);
        $this->addControl($result, [NotificationSwitchLink::class, ['content' => $this->contentObject], ['sortOrder' => 300]]);
        $this->addControl($result, [PermaLink::class, ['content' => $this->contentObject], ['sortOrder' => 400]]);
        $this->addControl($result, [PinLink::class, ['content' => $this->contentObject], ['sortOrder' => 500]]);
        $this->addControl($result, [ArchiveLink::class, ['content' => $this->contentObject], ['sortOrder' => 600]]);

        if(isset($this->controlsOptions['add'])) {
            foreach ($this->controlsOptions['add'] as $linkOptions) {
                $this->addControl($result, $linkOptions);
            }
        }

        return $result;
    }

    protected function addControl(&$result, $options) {
        if(isset($this->controlsOptions['prevent']) && in_array($options[0], $this->controlsOptions['prevent'])) {
            return;
        }

        $result[] = $options;
    }

    /**
     * Renders the wall entry output 
     * 
     * @return string the output
     * @throws \Exception
     */
    public function renderWallEntry()
    {
        ob_start();
        ob_implicit_flush(false);
        try {
            $out = $this->render($this->wallEntryLayout, $this->getWallEntryViewParams());
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean() . $out;
    }

    /**
     * Returns the view paramters for the wall entry layout
     * 
     * @return array the view parameter array
     */
    public function getWallEntryViewParams()
    {
        $showContentContainer = false;
        $content = $this->contentObject->content;
        $user = $content->createdBy;
        $container = $content->container;

        // In case of e.g. dashboard, show contentContainer of this content
        if (!Yii::$app->controller instanceof ContentContainerController && !($container instanceof User && $container->id == $user->id)) {
            $showContentContainer = true;
        }

        $createdAt = $content->created_at;
        $updatedAt = null;
        // Note this won't work if updated_at is of type yii/db/Expression (NOW()) in this case the content has to be refreshed manually.
        if ($createdAt !== $content->updated_at && !empty($content->updated_at) && is_string($content->updated_at)) {
            $updatedAt = $content->updated_at;
        }

        $addonOptions = ['object' => $this->contentObject, 'widgetOptions' => $this->addonOptions];

        return [
            'content' => $this->run(),
            'object' => $this->contentObject,
            'wallEntryWidget' => $this,
            'showContentContainer' => $showContentContainer,
            'user' => $user,
            'renderControls' => $this->renderControls,
            'renderAddons' => $this->renderAddons,
            'addonOptions' => $addonOptions,
            'container' => $container,
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt
        ];
    }

}
