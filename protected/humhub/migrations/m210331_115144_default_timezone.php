<?php

use yii\db\Migration;

/**
 * Class m210331_115144_default_timezone
 */
class m210331_115144_default_timezone extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('INSERT INTO setting (name, value, module_id)
            SELECT "defaultTimeZone", value, module_id
              FROM setting
             WHERE name = "timeZone"
               AND module_id = "base"');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210331_115144_default_timezone cannot be reverted.\n";

        return false;
    }
}
