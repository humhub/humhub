<?php

use yii\db\Migration;

/**
 * Class m210721_055137_content_disabled_comments
 */
class m210721_055137_content_disabled_comments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('content', 'disabled_comments', $this->boolean()->defaultValue(false)->after('archived'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('content', 'disabled_comments');
    }

}
