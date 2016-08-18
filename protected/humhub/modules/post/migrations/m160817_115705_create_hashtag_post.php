<?php

use yii\db\Migration;

class m160817_115705_create_hashtag_post extends Migration
{
    public function up()
    {
        $this->createTable('hashtag_post', [
            'id' => $this->primaryKey(),
            'tag' => $this->string(250),
            'post_id' => $this->integer()
        ]);

        $this->addForeignKey('fk_hashtag_post_post', 'hashtag_post', 'post_id', 'post', 'id', 'CASCADE', 'CASCADE');

    }

    public function down()
    {
        $this->dropTable('hashtag_post');
    }
}
