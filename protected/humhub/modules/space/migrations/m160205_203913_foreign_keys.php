<?php

use yii\db\Schema;
use yii\db\Migration;

class m160205_203913_foreign_keys extends Migration
{

    public function up()
    {
        // Cleanup orphaned records
        $this->db->createCommand('DELETE space_module FROM space_module LEFT JOIN space s ON s.id=space_module.space_id WHERE s.id IS NULL and space_module.space_id != 0')->execute();

        try {
            $this->addForeignKey('fk_space_membership-user_id', 'space_membership', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }

        try {
            $this->addForeignKey('fk_space_membership-space_id', 'space_membership', 'space_id', 'space', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }

        try {
            $this->alterColumn('space_module', 'space_id', $this->integer()->null());
            $this->update('space_module', ['space_id' => new yii\db\Expression('NULL')], ['space_id' => 0]);
            $this->addForeignKey('fk_space_module-space_id', 'space_module', 'space_id', 'space', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }

        try {
            $this->addForeignKey('fk_space-wall_id', 'space', 'wall_id', 'wall', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }

        try {
            $this->addForeignKey('fk_space_module-module_id', 'space_module', 'module_id', 'module_enabled', 'module_id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }
    }

    public function down()
    {
        $this->dropForeignKey('fk_space_membership-user_id', 'space_membership');
        $this->dropForeignKey('fk_space_membership-space_id', 'space_membership');
        $this->dropForeignKey('fk_space_module-space_id', 'space_module');
        $this->dropForeignKey('fk_space-wall_id', 'space');
        $this->dropForeignKey('fk_space_module-module_id', 'space_module');

        return true;
    }

    /*
      // Use safeUp/safeDown to run migration code within a transaction
      public function safeUp()
      {
      }

      public function safeDown()
      {
      }
     */
}
