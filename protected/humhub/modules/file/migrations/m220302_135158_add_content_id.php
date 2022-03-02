<?php

use humhub\components\Migration;

/**
 * Class m220302_135158_add_content_id
 */
class m220302_135158_add_content_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn('file', 'content_id', $this->integer()->unsigned()->after('object_id'));

        $this->execute('UPDATE file
            INNER JOIN content
               ON file.object_model = content.object_model
              AND file.object_id = content.object_id
              SET file.content_id = content.id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropColumn('file', 'content_id');
    }
}
