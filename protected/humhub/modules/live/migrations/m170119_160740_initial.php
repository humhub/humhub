<?php

use yii\db\Migration;

class m170119_160740_initial extends Migration
{

    public function up()
    {
        $this->createTable('live', [
            'id' => $this->primaryKey(),
            'contentcontainer_id' => $this->integer()->null(),
            'visibility' => $this->integer(1)->null(),
            'serialized_data' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull()
        ]);
        $this->addForeignKey('contentcontainer', 'live', 'contentcontainer_id', 'contentcontainer', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        echo "m170119_160740_initial cannot be reverted.\n";

        return false;
    }

}
