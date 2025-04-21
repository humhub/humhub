<?php

use yii\db\Migration;

class m250421_120106_add_version_column_to_post_and_comment_tables extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%post}}', 'version', $this->integer()->notNull()->defaultValue(1));
        $this->addColumn('{{%comment}}', 'version', $this->integer()->notNull()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%post}}', 'version');
        $this->dropColumn('{{%comment}}', 'version');
    }
}
