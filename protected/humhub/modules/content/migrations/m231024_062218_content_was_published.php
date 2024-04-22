<?php

use humhub\components\Migration;

/**
 * Class m231024_062218_content_was_published
 */
class m231024_062218_content_was_published extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn(
            'content',
            'was_published',
            $this->boolean()->defaultValue(false)->notNull()->after('state'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropColumn('content', 'was_published');
    }
}
