<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use yii\db\Migration;

/**
 * Class m190309_201944_rename_settings
 */
class m190309_201944_rename_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $renameSettings = [
            'auth.ldap.enabled' => 'enabled',
            'auth.ldap.refreshUsers' => 'refreshUsers',
            'auth.ldap.hostname' => 'hostname',
            'auth.ldap.port' => 'port',
            'auth.ldap.encryption' => 'encryption',
            'auth.ldap.username' => 'username',
            'auth.ldap.password' => 'password',
            'auth.ldap.baseDn' => 'baseDn',
            'auth.ldap.loginFilter' => 'loginFilter',
            'auth.ldap.userFilter' => 'userFilter',
            'auth.ldap.usernameAttribute' => 'usernameAttribute',
            'auth.ldap.emailAttribute' => 'emailAttribute',
            'auth.ldap.idAttribute' => 'idAttribute',
        ];

        foreach ($renameSettings as $from => $to) {
            $this->update('setting', ['name' => $to, 'module_id' => 'ldap'], ['name' => $from, 'module_id' => 'user']);
        }

    }



    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190309_201944_rename_settings cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190309_201944_rename_settings cannot be reverted.\n";

        return false;
    }
    */
}
