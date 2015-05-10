<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * Description of SearchResult
 *
 * @author luke
 */
class SearchResultSet
{

    public $results = array();
    public $total;
    public $page;
    public $pageSize;

    public function getResultInstances()
    {
        $instances = array();

        foreach ($this->results as $result) {
            $modelClass = $result->model;
            $model = call_user_func(array($modelClass, 'model'));

            $instance = $model->findByPk($result->pk);
            if ($instance !== null) {
                $instances[] = $instance;
            } else {
                Yii::log('Could not load search result ' . $result->model . " - " . $result->pk, CLogger::LEVEL_ERROR);
            }
        }

        return $instances;
    }

}
