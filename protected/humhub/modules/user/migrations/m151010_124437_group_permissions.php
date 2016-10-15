<?php

use yii\db\Schema;


class m151010_124437_group_permissions extends \humhub\components\Migration
{
    public function up()
    {
        $this->createTable('group_permission', array(
            'permission_id' => $this->string(150)->notNull(),
            'group_id' => Schema::TYPE_INTEGER,
            'module_id' => $this->string(50)->notNull(),
            'class' => Schema::TYPE_STRING,
            'state' => Schema::TYPE_BOOLEAN,
        ));

        $this->addPrimaryKey('permission_pk', 'group_permission', ['permission_id', 'group_id', 'module_id']);
        
        $groups = (new \yii\db\Query())->select("group.*")->from('group');
        foreach ($groups->each() as $group) {
            if ($group['can_create_public_spaces'] != 1) {
                $this->insertSilent('group_permission', [
                    'permission_id' => 'create_public_space',
                    'group_id' => $group['id'],
                    'module_id' => 'space',
                    'class' => 'humhub\modules\space\permissions\CreatePublicSpace',
                    'state' => '0'
                ]);
            }
            if ($group['can_create_private_spaces'] != 1) {
                $this->insertSilent('group_permission', [
                    'permission_id' => 'create_private_space',
                    'group_id' => $group['id'],
                    'module_id' => 'space',
                    'class' => 'humhub\modules\space\permissions\CreatePrivateSpace',
                    'state' => '0'
                ]);
            }
        }     
        
        $this->dropColumn('group', 'can_create_public_spaces');
        $this->dropColumn('group', 'can_create_private_spaces');
        
    }

    public function down()
    {
        echo "m151010_124437_group_permissions cannot be reverted.\n";

        return false;
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
