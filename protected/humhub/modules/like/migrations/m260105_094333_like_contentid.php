<?php

use humhub\components\Migration;

class m260105_094333_like_contentid extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn('like', 'content_id', $this->integer()->after('id'));
        $this->alterColumn('like', 'object_model', $this->string(100)->null());
        $this->alterColumn('like', 'object_id', $this->integer()->null());

        $this->execute(
            'UPDATE `like`
         LEFT JOIN `content` ON like.object_id = like.object_id AND content.object_model=like.object_model
         SET like.content_id=content.id, like.object_model=NULL, like.object_id=NULL
         WHERE content.object_model IS NOT NULL'
        );

        $this->safeAddForeignKey('fk_like_content', 'like', 'content_id', 'content', 'id', 'RESTRICT', 'CASCADE');
        $this->safeDropForeignKey('fk_like-target_user_id', 'like');
        $this->safeDropColumn('like', 'target_user_id');
        $this->safeDropColumn('like', 'updated_by');
        $this->safeDropColumn('like', 'updated_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260105_094333_like_contentid cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260105_094333_like_contentid cannot be reverted.\n";

        return false;
    }
    */
}
