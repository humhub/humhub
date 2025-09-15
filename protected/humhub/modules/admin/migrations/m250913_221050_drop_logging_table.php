<?php

use humhub\components\Migration;

/**
 * Handles the dropping of table `{{%logging}}`.
 */
class m250913_221050_drop_logging_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeDropTable('{{%logging}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250913_221050_drop_logging_table cannot be reverted.\n";

        return false;
    }
}
