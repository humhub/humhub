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

class StreamEntryResponse extends Model
{
    public $id;
    public $guid;
    public $output;
    public $pinned;
    public $archived;
    public $content;

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
            'archived' => (boolean) $content->archived
        ]))->asArray();
    }

    public function asArray()
    {
        return [
            'id' => $this->id,
            'guid' => $this->guid,
            'output' => $this->output,
            'pinned' => (boolean) $this->pinned,
            'archived' => (boolean) $this->archived
        ];
    }

    public function asJson()
    {
        return Yii::$app->controller->asJson($this->asArray());
    }
}
