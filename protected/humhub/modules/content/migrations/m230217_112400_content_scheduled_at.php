<?php

use humhub\components\Migration;
use humhub\modules\content\models\Content;

/**
 * Class m230217_112400_content_scheduled_at
 */
class m230217_112400_content_scheduled_at extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn(Content::tableName(), 'scheduled_at', $this
            ->dateTime()
            ->null()
            ->after('locked_comments'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropColumn(Content::tableName(), 'scheduled_at');
    }
}
