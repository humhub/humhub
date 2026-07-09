<?php

use humhub\components\Migration;

/**
 * Adds the optional `title` column to the `post` table.
 */
class m260522_185040_add_post_title extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('post', 'title', $this->string(255)->null()->after('id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('post', 'title');
    }
}
