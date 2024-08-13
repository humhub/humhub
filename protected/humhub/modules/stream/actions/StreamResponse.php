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
     * @var string
     */
    public $error;

    /**
     * @var int
     */
    public $errorCode;

    /**
     * StreamResponse constructor.
     * @param StreamQuery $streamQuery
     */
    public function __construct(StreamQuery $streamQuery)
    {
        $this->streamQuery = $streamQuery;
    }

    /**
     * Adds entries to the response by providing either an response array or a StreamEntryResponse instance.
     * An $entry array needs at least to provide a `id` and `output` value. When injecting an entry outside of the
     * stream query result the $injectIndex should be set to either an existing result index or to true. This will
     * make sure the injected entry will be ignored in the stream flow (load more).
     *
     * @param array|StreamEntryResponse $entry
     * @param bool|int $injectIndex
     * @throws Exception
     */
    public function addEntry($entry, $injectIndex = false)
    {
        if($entry instanceof StreamEntryResponse) {
            $entry = $entry->asArray();
        }

        $entryId = $entry['id'];
        $this->entries[$entryId] = $entry;

        if(is_int($injectIndex)) {
            array_splice( $this->entryOrder, $injectIndex, 0, $entryId );
        } else {
            $this->entryOrder[] = $entryId;
        }

        if($injectIndex === false) {
            $this->lastContentId = $entry['id'];
        }
    }

    /**
     * Can be used to set error information.
     * @param $code int
     * @param $msg string
     */
    public function setError($code, $msg)
    {
        $this->error = $msg;
        $this->errorCode = $code;
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

        if ($this->error) {
            $this->result['error'] = $this->error;
        }

        if ($this->errorCode) {
            $this->result['errorCode'] = $this->errorCode;
        }

        if ($this->streamQuery->hasErrors()) {
            $this->result['filterErrors'] = $this->streamQuery->getErrors();
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
