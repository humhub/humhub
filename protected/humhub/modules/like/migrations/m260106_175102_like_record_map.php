<?php

use humhub\components\Migration;

class m260106_175102_like_record_map extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute(
            'INSERT IGNORE INTO record_map (`model`, `pk`) SELECT DISTINCT l.object_model, l.object_id FROM `like` l WHERE l.object_model IS NOT NULL AND l.object_model != "";',
        );

        $this->safeAddColumn('like', 'content_addon_record_id', $this->integer()->null()->after('content_id'));

        $this->execute(
            'UPDATE `like` l JOIN record_map rm ON rm.`model` = l.object_model AND rm.`pk` = l.object_id SET l.content_addon_record_id = rm.id WHERE l.content_addon_record_id IS NULL AND l.object_model IS NOT NULL AND l.object_model != "";',
        );

        $this->safeAddForeignKey('fk_like_content_addon', 'like', 'content_addon_record_id', 'record_map', 'id', 'CASCADE', 'CASCADE');

        $this->dropIndex('index_object', 'like');
        $this->dropIndex('unique-object-user', 'like');
        $this->createIndex('idx_unique_user', 'like', ['content_id', 'content_addon_record_id', 'created_by'], true);

        $this->safeDropColumn('like', 'object_model');
        $this->safeDropColumn('like', 'object_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260106_175102_like_record_map cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260106_175102_like_record_map cannot be reverted.\n";

        return false;
    }
    */
}
