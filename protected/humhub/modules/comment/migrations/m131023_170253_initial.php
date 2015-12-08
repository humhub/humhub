<?php

use yii\db\Schema;
use yii\db\Migration;

class m131023_170253_initial extends Migration
{

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('comment', array(
            'id' => 'pk',
            'message' => 'text DEFAULT NULL',
            'object_model' => 'varchar(100) NOT NULL',
            'object_id' => 'int(11) NOT NULL',
            'space_id' => 'int(11) DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
        ), $tableOptions);
    }

    public function down()
    {
        $this->dropTable('comment');
    }

}
