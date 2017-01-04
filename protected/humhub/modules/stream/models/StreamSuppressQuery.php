<?php

namespace humhub\modules\stream\models;

use humhub\modules\stream\models\StreamQuery;

/**
 * StreamSuppressQuery detects same content types in a row and trims the output.
 * 
 * E.g. if there are 5 files in a row, only two files will be returned.
 * All following files are stored and can be obtained via method getSuppressed().
 * 
 * @see \humhub\modules\stream\actions\Stream
 * @author luke
 * @since 1.2
 */
class StreamSuppressQuery extends StreamQuery
{

    /**
     * @var boolean marks query as executed
     */
    protected $isQueryExecuted = false;

    /**
     * @var array suppressed contents (format: [displayedContentId] = [suppressedContentId1, suppressedContentId2])
     */
    protected $suppressions = [];

    /**
     * @var int the last returned content id
     */
    protected $lastContentId;

    /**
     * @inheritdoc
     */
    public function all()
    {
        // Only suppress on 3 or more contents to deliever
        if ($this->limit < 3) {
            return parent::all();
        }

        if (!$this->_built) {
            $this->setupQuery();
        }

        $results = [];
        $originalLimit = $this->limit;

        // increase limit
        $this->limit = $this->limit + 30;

        foreach ($this->_query->batch($originalLimit) as $contents) {
            foreach ($contents as $content) {
                $this->lastContentId = $content->id;

                if (!$this->isSuppressed($results, $content)) {
                    $results[] = $content;

                    // Enough results collected
                    if (count($results) === $originalLimit) {
                        break 2;
                    }
                }
            }
        }

        $this->isQueryExecuted = true;

        return $results;
    }

    /**
     * Checks if this content should be suppressed
     * 
     * @param type $results
     * @param type $content
     */
    protected function isSuppressed(&$results, $content)
    {
        // TODO: Make configurable
        $doNotSuppress = [\humhub\modules\post\models\Post::className()];

        // Check if content type is suppressable
        if (in_array($content->object_model, $doNotSuppress)) {
            return false;
        }

        // Checks if previous two contents have the same content class model
        $c = count($results) - 1;
        if ($c >= 1 && $results[$c - 1]->object_model === $results[$c]->object_model && $content->object_model === $results[$c]->object_model) {
            $this->suppressions[$results[$c]->id][] = $content->id;
            return true;
        }

        return false;
    }

    /**
     * Returns suppressed content ids
     * 
     * @return array
     * @throws \yii\base\Exception
     */
    public function getSuppressions()
    {
        if (!$this->isQueryExecuted) {
            throw new \yii\base\Exception("Execute query first via all() method before reading suppressed items.");
        }

        return $this->suppressions;
    }

    /**
     * Returns the last content id of the stream query.
     * It may also contains a suppressed content id.
     * 
     * @return int content id
     */
    public function getLastContentId()
    {
        return $this->lastContentId;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'StreamQuery';
    }

}
