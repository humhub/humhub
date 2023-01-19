<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\queue\models;

/**
 * This is the model class for table "queue_exclusive".
 *
 * @property string $id
 * @property string $job_message_id
 * @property integer $job_status
 * @property string $last_update
 */
class QueueExclusive extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'queue_exclusive';
    }

}
