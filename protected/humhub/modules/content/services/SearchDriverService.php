<?php

namespace humhub\modules\content\services;

use humhub\components\Event;
use humhub\modules\content\events\ContentEvent;
use humhub\modules\content\jobs\SearchDeleteDocument;
use humhub\modules\content\jobs\SearchUpdateDocument;
use humhub\modules\content\models\Content;
use humhub\modules\content\Module;
use humhub\modules\content\search\driver\AbstractDriver;
use Yii;
use yii\base\Component;

class SearchDriverService extends Component
{
    public const EVENT_BEFORE_UPDATE = 'update';
    public const EVENT_BEFORE_DELETE = 'delete';

    private readonly AbstractDriver $driver;

    public function __construct($config = [])
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('content');
        $this->driver = $module->getSearchDriver();

        return parent::__construct($config);
    }

    public function getDriver(): AbstractDriver
    {
        return $this->driver;
    }

    public function update(Content $content, bool $async = false): void
    {
        if ($async) {
            Yii::$app->queue->push(new SearchUpdateDocument(['contentId' => $content->id]));
            return;
        }

        $this->trigger(self::EVENT_BEFORE_UPDATE, new ContentEvent(['content' => $content]));
        $this->driver->update($content);
    }

    public function delete(int $contentId, bool $async = false): void
    {
        if ($async) {
            Yii::$app->queue->push(new SearchDeleteDocument(['contentId' => $contentId]));
            return;
        }

        $this->trigger(self::EVENT_BEFORE_DELETE, new Event(['data' => ['contentId' => $contentId]]));
        $this->driver->delete($contentId);
    }

}
