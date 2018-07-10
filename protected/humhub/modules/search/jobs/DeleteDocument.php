<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\jobs;

use humhub\components\ActiveRecord;
use humhub\modules\queue\ActiveJob;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\search\interfaces\Searchable;
use Yii;

/**
 * DeleteDocument triggers a delete in the search index.
 *
 * @since 1.3
 * @author Luke
 */
class DeleteDocument extends ActiveJob implements ExclusiveJobInterface
{

    /**
     * @var string class name of the active record
     */
    public $activeRecordClass;

    /**
     * @var int the primary key of the active record
     */
    public $primaryKey;


    /**
     * @inhertidoc
     */
    public function getExclusiveJobId()
    {
        return 'search.delete.' . md5($this->activeRecordClass . $this->primaryKey);
    }

    /**
     * @inhertidoc
     */
    public function run()
    {
        $class = $this->activeRecordClass;
        if (is_subclass_of($class, ActiveRecord::class)) {
            $record = $class::findOne(['id' => $this->primaryKey]);
            if ($record !== null && $record instanceof Searchable) {
                Yii::$app->search->delete($record);
            }
        }
    }

}
