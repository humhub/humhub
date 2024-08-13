<?php

use humhub\components\Migration;
use humhub\modules\comment\models\Comment;

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

        // Root comments
        $this->execute('UPDATE file
            INNER JOIN comment
               ON file.object_model = :commentModel
              AND file.object_id = comment.id
            INNER JOIN content
               ON comment.object_model = content.object_model
              AND comment.object_id = content.object_id
              SET file.content_id = content.id', [
            'commentModel' => Comment::class
        ]);

        // Sub comments
        $this->execute('UPDATE file
            INNER JOIN comment AS c1
               ON file.object_model = :commentModel
              AND file.object_id = c1.id
              AND c1.object_model = :commentModel
            INNER JOIN comment AS c2
              ON c1.object_id = c2.id
            INNER JOIN content
               ON c2.object_model = content.object_model
              AND c2.object_id = content.object_id
              SET file.content_id = content.id', [
            'commentModel' => Comment::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropColumn('file', 'content_id');
    }
}
