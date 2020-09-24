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
     * Used when rendering the entry on the dashboard
     */
    const VIEW_CONTEXT_DASHBOARD = 'dashboard';

    /**
     * Used when rendering the entry outside of its container (similar to dashboard)
     */
    const VIEW_CONTEXT_GLOBAL = 'global';

    /**
     * Used when rendering the entry in the context of its container (default)
     */
    const VIEW_CONTEXT_CONTAINER = 'container';

    /**
     * @var string the active view mode of this stream entry
     */
    public $viewMode;

    /**
     * @var string used to overwrite the widget class while rendering
     */
    public $widgetClass = null;

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
            $this->viewMode = $base->viewMode;
            $this->widgetClass = $base->widgetClass;
        }

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
        if(!$this->viewMode) {
            $this->viewMode = Yii::$app->request->get('from', static::VIEW_CONTEXT_CONTAINER);
        }
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

    public function getStreamEntryWidgetClass()
    {
        return $this->widgetClass;
    }

    /**
     * Sets the viewMode
     * @param $viewMode
     * @return $this
     */
    public function viewMode($viewMode)
    {
        $this->viewMode = $viewMode;
        return $this;
    }

    public function getViewMode()
    {
        return $this->viewMode;
    }

    public function isViewMode($viewMode) {
        return $this->viewMode === $viewMode;
    }

    /**
     * @param ContentActiveRecord $model
     * @return bool whether or not this entry should be displayed as pinned
     */
    public function isPinned(ContentActiveRecord $model)
    {
        $currentContainer = ContentContainerHelper::getCurrent();
        $content = $model->content;
        return $currentContainer
            && $this->isViewMode(static::VIEW_CONTEXT_CONTAINER)
            && $content->isPinned()
            && $currentContainer->contentcontainer_id === $content->contentcontainer_id;
    }


}
