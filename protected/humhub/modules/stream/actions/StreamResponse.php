<?php


namespace humhub\modules\stream\actions;


use humhub\modules\stream\models\StreamQuery;
use humhub\modules\stream\models\StreamSuppressQuery;
use Yii;

class StreamResponse
{
    private $result = [];
    private $entries = [];

    /**
     * @var StreamQuery
     */
    private $streamQuery;

    public function __construct(StreamQuery $streamQuery)
    {
        $this->streamQuery = $streamQuery;
    }

    /**
     * @param $contentId
     * @param array $entry
     */
    public function addEntry($contentId, $entry)
    {
        $this->entries[$contentId] = $entry;
    }

    public function asArray()
    {
        $this->result['content'] = $this->entries;
        $this->result['contentOrder'] = array_keys($this->entries);
        $this->result['lastContentId'] = end($this->result['contentOrder']);
        $this->isLast(count($this->entries) < $this->streamQuery->limit);

        if ($this->streamQuery instanceof StreamSuppressQuery && !$this->streamQuery->isSingleContentQuery()) {
            $this->result['contentSuppressions'] = $this->streamQuery->getSuppressions();
            $this->result['lastContentId'] = $this->streamQuery->getLastContentId();
        }
        return $this->result;
    }

    public function asJson()
    {
        return Yii::$app->controller->asJson($this->asArray());
    }

    public function isLast($isLast)
    {
        $this->result['isLast'] = $isLast;
    }

}
