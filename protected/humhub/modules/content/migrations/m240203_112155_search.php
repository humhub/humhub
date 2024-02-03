<?php

use yii\db\Migration;

/**
 * Class m240203_112155_search
 */
class m240203_112155_search extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('content_fulltext', [
            'content_id' => $this->integer(),
            'contents' => $this->text(),
            'comments' => $this->text(),
            'files' => $this->text(),
        ]);

        $this->execute("ALTER TABLE content_fulltext ADD FULLTEXT INDEX ftx (contents ASC, comments ASC, files ASC)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240203_112155_search cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240203_112155_search cannot be reverted.\n";

        return false;
    }
    */
}
