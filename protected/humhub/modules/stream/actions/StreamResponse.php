<?php


namespace humhub\modules\stream\actions;


use humhub\modules\stream\models\StreamQuery;
use humhub\modules\stream\models\StreamSuppressQuery;
use Yii;
use yii\base\Exception;

/**
 * This class is used to build up a stream array or json response used in stream actions.
 *
 * @package humhub\modules\stream\actions
 * @since 1.7
 */
class StreamResponse
{
    /**
     * @var array contains the result array of the different entries
     */
    public $entries = [];

    /**
     * @var array contains the order of entries
     */
    public $entryOrder = [];

    /**
     * @var array resulting array
     */
    private $result = [];

    /**
     * @var StreamQuery the StreamQuery used to fetch the entries
     */
    private $streamQuery;

    /**
     * @var int
     */
    private $lastContentId;

    /**
     * StreamResponse constructor.
     * @param StreamQuery $streamQuery
     */
    public function __construct(StreamQuery $streamQuery)
    {
        $this->streamQuery = $streamQuery;
    }

    /**
     * @param $entryId
     * @param array|StreamEntryResponse $entry
     * @param int $index
     * @throws Exception
     */
    public function addEntry($entry, $index = null)
    {
        if($entry instanceof StreamEntryResponse) {
            $entry = $entry->asArray();
        }

        $entryId = $entry['id'];
        $this->entries[$entryId] = $entry;

        if($index !== null) {
            array_splice( $this->entryOrder, $index, 0, $entryId );
        } else {
            $this->entryOrder[] = $entryId;
        }

        if(!$index && isset($entry['isContent']) && $entry['isContent']) {
            $this->lastContentId = $entry['id'];
        }
    }

    /**
     * Returns the stream response array.
     * @return array
     * @throws Exception
     */
    public function asArray()
    {
        $this->result['content'] = $this->entries;
        $this->result['contentOrder'] = $this->entryOrder;
        $this->result['lastContentId'] = $this->lastContentId;
        $this->isLast(count($this->entries) < $this->streamQuery->limit);

        if ($this->streamQuery instanceof StreamSuppressQuery && !$this->streamQuery->isSingleContentQuery()) {
            $this->result['contentSuppressions'] = $this->streamQuery->getSuppressions();
            $this->result['lastContentId'] = $this->streamQuery->getLastContentId();
        }
        return $this->result;
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

    /**
     * @param $isLast boolean sets the isLast flag of the response
     */
    private function isLast($isLast)
    {
        $this->result['isLast'] = $isLast;
    }

}
