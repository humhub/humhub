<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\libs;

use humhub\modules\search\interfaces\Searchable;
use humhub\modules\search\jobs\DeleteDocument;
use humhub\modules\search\jobs\UpdateDocument;
use Yii;
use yii\base\BaseObject;
use yii\db\ActiveRecord;

/**
 * SearchHelper
 *
 * @since 1.2.3
 * @author Luke
 */
class SearchHelper extends BaseObject
{

    /**
     * Checks if given text matches a search query.
     *
     * @param string $query
     * @param string $text
     * @return boolean
     */
    public static function matchQuery($query, $text)
    {
        foreach (explode(" ", $query) as $keyword) {
            if (!empty($keyword) && strpos($text, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Queues search index update of an active record
     *
     * @param ActiveRecord $record
     * @return bool
     */
    public static function queueUpdate(ActiveRecord $record)
    {
        if ($record instanceof Searchable) {
            $pk = $record->getPrimaryKey();
            if (!empty($pk) && !is_array($pk)) {
                Yii::$app->queue->push(new UpdateDocument([
                    'activeRecordClass' => get_class($record),
                    'primaryKey' => $pk
                ]));
                return true;
            }
        }
        return false;
    }

    /**
     * Queues search index delete of an active record
     *
     * @param ActiveRecord $record
     * @return bool
     */
    public static function queueDelete(ActiveRecord $record)
    {
        if ($record instanceof Searchable) {
            $pk = $record->getPrimaryKey();
            if (!empty($pk) && !is_array($pk)) {
                Yii::$app->queue->push(new DeleteDocument([
                    'activeRecordClass' => get_class($record),
                    'primaryKey' => $pk
                ]));
                return true;
            }
        }
        return false;
    }


}
