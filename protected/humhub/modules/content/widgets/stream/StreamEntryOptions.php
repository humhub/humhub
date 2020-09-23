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
    private $viewMode;

    /**
     * @var string used to overwrite the widget class while rendering
     */
    private $entryWidgetClass = null;

    public function __construct(StreamEntryOptions $base = null, $config = [])
    {
        if($base) {
            $this->viewMode = $base->viewMode;
            $this->entryWidgetClass = $base->entryWidgetClass;
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
    public function overWriteStreamWidgetClass($entryWidgetClass)
    {
        $this->entryWidgetClass = $entryWidgetClass;
        return $this;
    }

    public function getStreamEntryWidgetClass()
    {
        return $this->entryWidgetClass;
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
