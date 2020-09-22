<?php

use yii\db\Schema;
use yii\db\Migration;

class m160205_203840_foreign_keys extends Migration
{

    public function up()
    {
        // Cleanup orphaned records
        $this->db->createCommand('DELETE user_module FROM user_module LEFT JOIN user u ON u.id=user_module.user_id WHERE u.id IS NULL AND user_module.user_id != 0')->execute();

        try {
            $this->addForeignKey('fk_user_follow-user_id', 'user_follow', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }

        try {
            $this->addForeignKey('fk_group-space_id', 'group', 'space_id', 'space', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }

        try {
            $this->addForeignKey('fk_group_permission-group_id', 'group_permission', 'group_id', 'group', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }

        try {
            $this->addForeignKey('fk_user_mentioning-user_id', 'user_mentioning', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }

        try {
            $this->alterColumn('user_module', 'user_id', $this->integer()->null());
            $this->update('user_module', ['user_id' => new yii\db\Expression('NULL')], ['user_id' => 0]);
            $this->addForeignKey('fk_user_module-user_id', 'user_module', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }

        try {
            $this->addForeignKey('fk_user_password-user_id', 'user_password', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }

        try {
            $this->addForeignKey('fk_profile-user_id', 'profile', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }

        try {
            $this->addForeignKey('fk_profile_field-profile_field_category_id', 'profile_field', 'profile_field_category_id', 'profile_field_category', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }

        try {
            $this->addForeignKey('fk_user_http_session-user_id', 'user_http_session', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }
    }

    public function down()
    {
        $this->dropForeignKey('fk_follow-user_id', 'user_follow');
        $this->dropForeignKey('fk_group-space_id', 'group');
        $this->dropForeignKey('fk_group_permission-group_id', 'group_permission');
        $this->dropForeignKey('fk_user_mentioning-user_id', 'user_mentioning');
        $this->dropForeignKey('fk_user_module-user_id', 'user_module');
        $this->dropForeignKey('fk_user_password-user_id', 'user_password');
        $this->dropForeignKey('fk_profile-user_id', 'profile');
        $this->dropForeignKey('fk_profile_field-profile_field_category_id', 'profile_field');

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
