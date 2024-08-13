<?php


namespace humhub\modules\content\widgets\stream;


use humhub\modules\content\components\ContentActiveRecord;
use humhub\widgets\JsWidget;
use Yii;

/**
 * This widget class serves as base class for all kind of wall entries.
 *
 * @package humhub\modules\content\widgets\stream
 * @since 1.7
 */
abstract class StreamEntryWidget extends JsWidget
{

    /**
     * @inheritDoc
     */
    public $jsWidget = 'stream.StreamEntry';

    /**
     * @var string defines the view used to render the root wrapper element of the wall entry
     */
    protected $layoutRoot = '@content/widgets/stream/views/streamEntryRootLayout';

    /**
     * @var string defines the element type of the root element, default is 'div'
     */
    protected $rootElement = 'div';

    /**
     * @var ContentActiveRecord the content type model to render.
     */
    public $model;

    /**
     * @var string class used for [[$renderOptions]]
     */
    protected $renderOptionClass = StreamEntryOptions::class;

    /**
     * @var StreamEntryOptions
     */
    public $renderOptions;

    /**
     * @return string rendered wall entry body without the layoutRoot wrapper
     */
    abstract protected function renderBody();

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        if(!$this->renderOptions || !is_a($this->renderOptions, $this->renderOptionClass, true)) {
            $optionClass = $this->renderOptionClass;
            $this->renderOptions = new $optionClass($this->renderOptions);
        } else {
            // Make sure we are using an own instance of renderOptions
           $this->renderOptions = clone $this->renderOptions;
        }
    }

    /**
     * Returns the wall output widget of this content.
     *
     * @param ContentActiveRecord $model
     * @param StreamEntryOptions|null $renderOptions
     * @param array $widgetParams
     * @return string
     * @throws \Exception
     */
    public static function renderStreamEntry(ContentActiveRecord $model, StreamEntryOptions $renderOptions = null, $widgetParams = [])
    {
        if(!is_a($model->wallEntryClass , static::class, true)) {
            return static::renderLegacyWallEntry($model, $widgetParams);
        }

        $widgetParams['model'] = $model;
        $widgetParams['renderOptions'] = $renderOptions;
        if($renderOptions && is_a($renderOptions->getStreamEntryWidgetClass(), static::class, true)) {
            $widgetParams['class'] = $renderOptions->getStreamEntryWidgetClass();
        }
        return call_user_func($model->wallEntryClass.'::widget', $widgetParams);
    }

    /**
     * @param ContentActiveRecord $record
     * @param array $options
     * @return string
     * @throws \Exception
     * @deprecated since 1.7 contains render logic for deprecated WallEntry widget
     */
    private static function renderLegacyWallEntry(ContentActiveRecord $record, $options = [])
    {
        if(!is_array($options)) {
            $options = [];
        }

        if (isset($options['jsWidget'])) {
            $jsWidget = $options['jsWidget'];
            unset($options['jsWidget']);
        } else {
            $jsWidget = $record->getWallEntryWidget()->jsWidget;
        }

        $params = [
            'content' => $record->getWallOut($options),
            'jsWidget' => $jsWidget,
            'entry' => $record->content
        ];

        return Yii::$app->controller->renderPartial('@humhub/modules/content/views/layouts/wallEntry', $params);
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        return $this->render($this->layoutRoot, [
            'model' => $this->model,
            'rootElement' => $this->rootElement,
            'bodyLayout' => $this->renderBody(),
            'options' => $this->getOptions()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $content = $this->model->content;

        $result = [
            'content-container-id' => $content->contentcontainer_id,
            'stream-entry' => 1,
            'stream-pinned' => (int) $this->renderOptions->isPinned($this->model),
            'content-key' => $content->id
        ];

        if($this->renderOptions->isInjected()) {
            $result['stream-injected'] = 1;
        }

        return $result;
    }
}
