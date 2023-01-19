<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use yii\db\Migration;

class m170723_133337_content_tag extends Migration
{
    public function safeUp()
    {
        $this->createTable('content_tag', [
            'id' => 'pk',
            'name' => $this->string(100)->notNull(),
            'module_id' => $this->string(100)->notNull(),
            'contentcontainer_id' => $this->integer()->null(),
            'type' => $this->string(100)->null(),
            'parent_id' => $this->integer()->null(),
            'color' => $this->string(7)->null()
        ]);

        $this->createIndex('idx-content-tag', 'content_tag', [
            'module_id', 'contentcontainer_id', 'name'
        ]);

        $this->addForeignKey('fk-content-tag-container-id', 'content_tag', 'contentcontainer_id', 'contentcontainer', 'id', 'CASCADE');
        $this->addForeignKey('fk-content-tag-parent-id', 'content_tag', 'parent_id', 'content_tag', 'id', 'SET NULL');

        $this->createTable('content_tag_relation', [
            'id' => 'pk',
            'content_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk-content-tag-rel-content-id', 'content_tag_relation', 'content_id', 'content', 'id', 'CASCADE');
        $this->addForeignKey('fk-content-tag-rel-tag-id', 'content_tag_relation', 'tag_id', 'content_tag', 'id', 'CASCADE');
    }

    public function safeDown()
    {
        echo "m170723_133337_content_filter cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170723_133337_content_filter cannot be reverted.\n";

        return false;
    }
    */
}
