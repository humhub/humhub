<?php


namespace humhub\modules\content\widgets\stream;


use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\stream\actions\Stream;
use humhub\widgets\JsWidget;
use Yii;
use yii\base\Model;

/**
 * This model can be used to influence the appearance of a wall entry by setting wall entry specific setting.
 *
 * @package humhub\modules\content\widgets\stream
 * @since 1.7
 */
class StreamEntryOptions extends Model
{

    /**
     * Used when rendering the entry in default context e.g. in a container stream (default)
     */
    const VIEW_CONTEXT_DEFAULT = 'default';

    /**
     * Used when rendering the entry on the dashboard
     */
    const VIEW_CONTEXT_DASHBOARD = 'dashboard';

    /**
     * Used when rendering the entry on the search stream
     */
    const VIEW_CONTEXT_SEARCH = 'search';

    /**
     * Used when rendering the entry e.g. as single stream entry
     */
    const VIEW_CONTEXT_DETAIL = 'detail';

    /**
     * Used when rendering the entry in a modal
     */
    const VIEW_CONTEXT_MODAL = 'modal';

    /**
     * @var string the active view context used for the stream entry rendering
     */
    public $viewContext;

    /**
     * @var string used to overwrite the widget class while rendering
     */
    public $widgetClass = null;

    /**
     * @var bool
     */
    public $isInjected = false;

    /**
     * StreamEntryOptions constructor.
     * @param StreamEntryOptions|array|null $base
     * @param array $config
     */
    public function __construct($base = null, $config = [])
    {
        if(is_array($base)) {
            $config = $base;
            $base = null;
        }

        if($base) {
            $this->viewContext = $base->viewContext;
            $this->widgetClass = $base->widgetClass;
        }

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        if(!$this->viewContext) {
            if (Yii::$app->request->isConsoleRequest) {
                $this->viewContext = static::VIEW_CONTEXT_DEFAULT;
            } else {
                $this->viewContext = Yii::$app->request->getViewContext() ?? static::VIEW_CONTEXT_DEFAULT;
            }
        }
    }

    /**
     * @param bool $val
     * @return $this
     */
    public function injected($val = true)
    {
        $this->isInjected = $val;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInjected()
    {
        return $this->isInjected;
    }

    /**
     * @param $entryWidgetClass
     * @return StreamEntryOptions
     */
    public function overwriteWidgetClass($entryWidgetClass)
    {
        $this->widgetClass = $entryWidgetClass;
        return $this;
    }

    /**
     * @return string|null the widget class used to overwrite the default stream entry widget class
     */
    public function getStreamEntryWidgetClass()
    {
        return $this->widgetClass;
    }

    /**
     * Sets the viewMode
     * @param $viewMode
     * @return $this
     */
    public function viewContext($viewMode)
    {
        $this->viewContext = $viewMode;
        return $this;
    }

    /**
     * @return string returns the active view context
     */
    public function getViewContext()
    {
        return $this->viewContext;
    }

    /**
     * Checks the active view mode against the $viewModes parameter.
     * The $viewModes parameter can either be an array of view modes or a single view mode string.
     * This function returns true, if one of the given view modes is active.
     *
     * @param string|array $viewMode
     * @return bool
     */
    public function isViewContext($viewContexts) {
        if(!is_array($viewContexts)) {
            return $this->viewContext === $viewContexts;
        }

        foreach ($viewContexts as $viewContext) {
            if($this->viewContext === $viewContext) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if additional container information should be displayed in the current context. This usually should be the
     * case if the entry is rendered outside of the related container stream.
     *
     * @param ContentActiveRecord $model
     * @return bool
     */
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

        if(!ContentContainerHelper::getCurrent()->is($model->content->container)) {
            return true;
        }

        return !$this->isViewContext(static::VIEW_CONTEXT_DEFAULT);
    }

    /**
     * @param ContentActiveRecord $model
     * @return bool whether or not this entry should be displayed as pinned in the current context
     * @throws \yii\base\Exception
     */
    public function isPinned(ContentActiveRecord $model)
    {
        $currentContainer = ContentContainerHelper::getCurrent();
        $content = $model->content;
        return $currentContainer
            && !$this->isInjected()
            && $this->isViewContext(static::VIEW_CONTEXT_DEFAULT)
            && $content->isPinned()
            && !$content->isArchived()
            && $currentContainer->contentcontainer_id === $content->contentcontainer_id;
    }


}
