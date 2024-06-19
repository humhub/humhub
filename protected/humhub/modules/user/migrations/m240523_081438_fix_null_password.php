<?php

use humhub\modules\user\models\Password;
use yii\db\Expression;
use yii\db\Migration;

/**
 * Class m240523_081438_fix_null_password
 */
class m240523_081438_fix_null_password extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Password::deleteAll(['OR',
            ['IS', 'algorithm', new Expression('NULL')],
            ['IS', 'password', new Expression('NULL')]]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240523_081438_fix_null_password cannot be reverted.\n";

        return false;
    }
}
