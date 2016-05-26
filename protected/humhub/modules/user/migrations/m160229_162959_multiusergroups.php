<?php

use yii\db\Schema;

class m160229_162959_multiusergroups extends \humhub\components\Migration
{
    public function up()
    {
        $this->createTable('group_user', array(
            'id' => 'pk',
            'user_id' => 'int(11) NOT NULL',
            'group_id' => 'int(11) NOT NULL',
            'is_group_admin' => 'tinyint(1) NOT NULL DEFAULT 0',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
        ), '');
        
        //Add indexes and foreign keys
        $this->createIndex('idx-group_user', 'group_user', ['user_id', 'group_id'], true);
        $this->addForeignKey('fk-user-group', 'group_user', 'user_id', 'user', 'id', 'CASCADE');
        $this->addForeignKey('fk-group-group', 'group_user', 'group_id', '`group`', 'id', 'CASCADE');
        
        //Merge old group user and group admins
        $this->execute('INSERT INTO group_user (user_id, group_id) SELECT DISTINCT user.id, user.group_id FROM user LEFT JOIN `group` ON user.group_id=group.id WHERE group.id IS NOT NULL');
        $this->execute('UPDATE group_user u SET is_group_admin = :value WHERE EXISTS (Select 1 FROM group_admin a WHERE u.user_id = a.user_id);', [':value' => 1]);
       
        //Add group columns
        $this->addColumn('group', 'is_admin_group', Schema::TYPE_BOOLEAN. ' NOT NULL DEFAULT 0');
        $this->addColumn('group', 'show_at_registration', Schema::TYPE_BOOLEAN. ' NOT NULL DEFAULT 1');
        $this->addColumn('group', 'show_at_directory', Schema::TYPE_BOOLEAN. ' NOT NULL DEFAULT 1');
        
        //Create initial administration group
        $this->insertSilent('group', [
            'name' => 'Administrator',
            'description' => 'Administrator Group',
            'is_admin_group' => '1',
            'show_at_registration' => '0',
            'show_at_directory' => '0',
            'created_at' => new \yii\db\Expression('NOW()')
        ]);
        
        //Determine administration group id
        $adminGroupId = (new \yii\db\Query())
                ->select('id')
                ->from('group')
                ->where(['is_admin_group' => '1'])
                ->scalar();
        
        //Load current super_admin user
        $rows = (new \yii\db\Query())
                ->select("id")
                ->from('user')
                ->where(['super_admin' => '1'])
                ->all();
        
        //Insert group_user for administartion groups for all current super_admins
        foreach($rows as $adminUserRow) {
            $this->insertSilent('group_user', ['user_id' => $adminUserRow['id'], 'group_id' => $adminGroupId, 'is_group_admin' => '1']);
        }
        
        //$this->insertSilent('group_permission', ['permission_id' => 'user_admin', 'group_id' => $adminGroupId, 'module_id' => 'user', 'class' => 'humhub\modules\user\permissions']);
        
        $this->dropTable('group_admin');
        $this->dropColumn('user', 'super_admin');
        $this->dropColumn('user', 'group_id');
    }

    public function down()
    {
        echo "m160229_162959_multiusergroups cannot be reverted.\n";

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
