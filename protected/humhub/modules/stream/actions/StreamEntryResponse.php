<?php


namespace humhub\modules\stream\actions;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\db\IntegrityException;
use yii\web\Response;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\content\widgets\stream\StreamEntryWidget;

/**
 * This class is used to build the stream entry array or json response used in stream actions.
 *
 * @package humhub\modules\stream\actions
 * @since 1.7
 */
class StreamEntryResponse extends Model
{
    /**
     * @var int content id
     */
    public $id;

    /**
     * @var string content guid
     */
    public $guid;

    /**
     * @var string rendered stream entry
     */
    public $output;

    /**
     * @var boolean content pinned flag
     */
    public $pinned;

    /**
     * @var boolean content archived flag
     */
    public $archived;

    /**
     * @var bool
     */
    public $isContent;

    /**
     * @var bool
     */
    public $isInjected;

    /**
     * @param Content $content
     * @param StreamEntryOptions|null $renderOptions
     * @param null $widgetOptions
     * @return Response
     * @throws Exception
     * @throws IntegrityException
     */
    public static function getAsJson(Content $content, StreamEntryOptions $renderOptions = null, $widgetOptions = null)
    {
        return Yii::$app->controller->asJson(static::getAsArray($content, $renderOptions, $widgetOptions));
    }

    /**
     * @param Content $content
     * @param StreamEntryOptions|null $renderOptions
     * @param null $widgetOptions
     * @return array
     * @throws Exception
     * @throws IntegrityException
     * @throws \Exception
     */
    public static function getAsArray(Content $content, StreamEntryOptions $renderOptions = null, $widgetOptions = null)
    {
        $model = $content->getModel();

        if (!$model) {
            throw new Exception('Could not get contents underlying object! - contentid: ' . $content->id);
        }

        $streamEntry = StreamEntryWidget::renderStreamEntry($model, $renderOptions, $widgetOptions);

        return (new static([
            'id' => $content->id,
            'guid' => $content->guid,
            'output' => Yii::$app->controller->renderAjaxContent($streamEntry),
            'pinned' => (boolean) $content->pinned,
            'archived' => (boolean) $content->archived,
            'isContent' => true
        ]))->asArray();
    }

    /**
     * Returns the stream entry response array.
     * @return array
     * @throws Exception
     */
    public function asArray()
    {
        return [
            'id' => $this->id,
            'guid' => $this->guid,
            'output' => $this->output,
            'pinned' => (boolean) $this->pinned,
            'archived' => (boolean) $this->archived,
            'isContent' => (boolean) $this->isContent,
            'isInjected' => (boolean) $this->isInjected,
        ];
    }

    /**
     * Returns the stream action result as json response.
     * @return \yii\web\Response
     * @throws Exception
     */
    public function asJson()
    {
        return Yii::$app->controller->asJson($this->asArray());
    }
}
