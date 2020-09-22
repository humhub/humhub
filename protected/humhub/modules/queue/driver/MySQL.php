<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\queue\driver;

use humhub\modules\queue\interfaces\QueueInfoInterface;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\queue\db\Queue;

/**
 * MySQL queue driver
 *
 * @since 1.2
 * @author Luke
 */
class MySQL extends Queue implements QueueInfoInterface
{

    /**
     * @inheritdoc
     */
    public $mutex = 'yii\mutex\MysqlMutex';

    /**
     * @return int the number of waiting jobs in the queue
     */
    public function getWaitingJobCount()
    {
        return (new Query())
            ->from($this->tableName)
            ->andWhere(['channel' => $this->channel])
            ->andWhere(['reserved_at' => null])
            ->andWhere(['delay' => 0])->count();
    }

    /**
     * @return int the number of delayed jobs in the queue
     */
    public function getDelayedJobCount()
    {
        return (new Query())
            ->from($this->tableName)
            ->andWhere(['channel' => $this->channel])
            ->andWhere(['reserved_at' => null])
            ->andWhere(['>', 'delay', 0])->count();
    }

    /**
     * @return int the number of reserved jobs in the queue
     */
    public function getReservedJobCount()
    {
        return (new Query())
            ->from($this->tableName)
            ->andWhere(['channel' => $this->channel])
            ->andWhere('[[reserved_at]] is not null')
            ->andWhere(['done_at' => null])->count();
    }

    /**
     * @return int the number of done jobs in the queue
     */
    public function getDoneJobCount()
    {
        $databaseName = (new Query())->select(new Expression('DATABASE()'))->scalar();
        $tableName = Yii::$app->db->schema->getRawTableName($this->tableName);

        $total = (new Query())
            ->select('AUTO_INCREMENT')
            ->from('INFORMATION_SCHEMA.TABLES')
            ->where(['TABLE_SCHEMA' => $databaseName, 'TABLE_NAME' => $tableName])
            ->scalar();

        return $total - $this->getWaitingJobCount() - $this->getDelayedJobCount() - $this->getReservedJobCount();
    }

}
