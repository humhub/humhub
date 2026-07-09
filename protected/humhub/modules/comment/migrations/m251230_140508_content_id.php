<?php

use humhub\modules\comment\models\Comment;
use humhub\components\Migration;

class m251230_140508_content_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn('comment', 'content_id', $this->integer()->after('id'));
        $this->safeAddColumn('comment', 'parent_comment_id', $this->integer()->after('content_id'));

        $this->alterColumn('comment', 'object_model', $this->string(100)->null());
        $this->alterColumn('comment', 'object_id', $this->integer()->null());

        $this->execute(
            'UPDATE `comment`
         LEFT JOIN `content` ON comment.object_id = content.object_id AND content.object_model=comment.object_model
         SET comment.content_id=content.id, comment.object_model=NULL, comment.object_id=NULL
         WHERE content.object_model IS NOT NULL',
        );

        $this->execute(
            'UPDATE `comment`
         SET comment.parent_comment_id=comment.object_id, comment.object_model=NULL, comment.object_id=NULL
         WHERE comment.object_model=:object_model',
            [':object_model' => Comment::class],
        );

        $this->execute(
            'UPDATE `comment`
         LEFT JOIN `comment` parent ON comment.parent_comment_id = parent.id
         SET comment.content_id=parent.content_id
         WHERE comment.parent_comment_id IS NOT NULL AND comment.content_id IS NULL AND parent.id IS NOT NULL',
        );


        $this->safeAddForeignKey('fk_comment_content', 'comment', 'content_id', 'content', 'id', 'RESTRICT', 'CASCADE');
        $this->safeAddForeignKey(
            'fk_comment_comment',
            'comment',
            'parent_comment_id',
            'comment',
            'id',
            'RESTRICT',
            'CASCADE',
        );

        $this->safeDropColumn('comment', 'object_model');
        $this->safeDropColumn('comment', 'object_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251230_140508_content_id cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251230_140508_content_id cannot be reverted.\n";

        return false;
    }
    */
}
