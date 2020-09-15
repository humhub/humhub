<?php


namespace humhub\modules\content\widgets\stream;


use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\widgets\JsWidget;

/**
 * Class BaseStreamEntryWidget
 * @package humhub\modules\content\widgets\stream
 * @since 1.7
 */
abstract class BaseStreamEntryWidget extends JsWidget
{
    /**
     * @inheritDoc
     */
    public $jsWidget = 'stream.StreamEntry';

    public $layoutRoot = '@content/widgets/stream/views/streamEntryRootLayout';

    /**
     * The content object
     *
     * @var ContentActiveRecord
     */
    public $model;

    public $renderOptions = [];


    protected  function getRenderOptions($key, $default = null)
    {
        return $this->renderOptions[$key] ?? $default;
    }
    /**
     * @return string
     */
    abstract protected function renderContentLayout();

    public function run()
    {
        return $this->render($this->layoutRoot, [
            'model' => $this->model,
            'rootElement' => $this->getRootElement(),
            'contentLayout' => $this->renderContentLayout(),
            'options' => $this->getOptions()
        ]);
    }

    protected function getRootElement()
    {
        return 'div';
    }

    public function getAttributes()
    {
        return [
            'class' => $this->isPinned() ? 'wall-entry pinned-entry' : 'wall-entry'
        ];
    }

    public function getData()
    {

        $content = $this->model->content;

        return [
            'content-container-id' => $content->contentcontainer_id,
            'stream-entry' => 1,
            'stream-pinned' => (int) $this->isPinned(),
            'content-key' => $content->id
        ];
    }

    protected function isPinned()
    {
        $container = ContentContainerHelper::getCurrent();
        $content = $this->model->content;
        return  $container && $content->isPinned() && $container->contentcontainer_id === $content->contentcontainer_id;
    }


}
