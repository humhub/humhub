<?php

use yii\db\Migration;

/**
 * Class m210506_060737_profile_field_directory_filter
 */
class m210506_060737_profile_field_directory_filter extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('profile_field', 'directory_filter', $this->tinyInteger(1)->notNull()->defaultValue(0));
        $this->createIndex('index_directory_filter', 'profile_field', 'directory_filter');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('profile_field', 'directory_filter');
    }

}
