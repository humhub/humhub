<?php

use humhub\components\Migration;

class m260106_160017_record_map extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeCreateTable('record_map', [
            'id' => $this->primaryKey(),
            'model' => $this->string(150)->notNull(),
            'pk' => $this->integer()->notNull(),
        ]);

        $this->safeCreateIndex('idx_record_map_model_pk', 'record_map', ['model', 'pk'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260106_160017_record_map cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260106_160017_record_map cannot be reverted.\n";

        return false;
    }
    */
}
