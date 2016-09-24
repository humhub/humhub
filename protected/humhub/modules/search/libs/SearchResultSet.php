<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\libs;

use humhub\components\ActiveRecord;
use Yii;

/**
 * SearchResultSet
 *
 * @author luke
 */
class SearchResultSet
{

    /**
     * @var SearchResult[] the search rsults
     */
    public $results = array();

    /**
     * @var int number of total results
     */
    public $total = 0;

    /**
     * @var int the current page
     */
    public $page = 1;

    /**
     * @var int page size
     */
    public $pageSize;


    /**
     * Returns active record instances of the search results
     * 
     * @return ActiveRecord[]
     */
    public function getResultInstances()
    {
        $instances = array();

        foreach ($this->results as $result) {
            /** @var $modelClass ActiveRecord */
            $modelClass = $result->model;
            $instance = $modelClass::findOne(['id' => $result->pk]);
            if ($instance !== null) {
                $instances[] = $instance;
            } else {
                Yii::error('Could not load search result ' . $result->model . " - " . $result->pk);
            }
        }

        return $instances;
    }

}
