<?php

use yii\db\Migration;
use yii\db\Expression;

class m160508_005740_settings_cleanup extends Migration
{

    public function up()
    {
        $this->dropColumn('setting', 'created_at');
        $this->dropColumn('setting', 'created_by');
        $this->dropColumn('setting', 'updated_at');
        $this->dropColumn('setting', 'updated_by');
        $this->alterColumn('setting', 'value', \yii\db\Schema::TYPE_TEXT);

        $this->update('setting', ['value' => new Expression('value_text')], 'value IS NULL and value_text IS NOT NULL');
        $this->dropColumn('setting', 'value_text');
        $this->createIndex('unique-setting', 'setting', ['name', 'module_id']);

        // Ensure default module_id is base
        $this->update('setting', ['module_id' => 'base'], ['module_id' => '']);
        $this->update('setting', ['module_id' => 'base'], ['module_id' => 'core']);
        $this->update('setting', ['module_id' => 'base'], ['IS', 'module_id', new Expression('NULL')]);

        // Fix wrong module_id 'share' module id
        $this->update('setting', ['name' => 'share.enable', 'module_id' => 'dashboard'], ['module_id' => 'share', 'name' => 'enable']);

        // Fix authentication module id
        $this->update('setting', ['name' => 'auth.internalUsersCanInvite', 'module_id' => 'user'], ['module_id' => 'authentication_internal', 'name' => 'internalUsersCanInvite']);
        $this->update('setting', ['name' => 'auth.needApproval', 'module_id' => 'user'], ['module_id' => 'authentication_internal', 'name' => 'needApproval']);
        $this->update('setting', ['name' => 'auth.anonymousRegistration', 'module_id' => 'user'], ['module_id' => 'authentication_internal', 'name' => 'anonymousRegistration']);
        $this->update('setting', ['name' => 'auth.defaultUserGroup', 'module_id' => 'user'], ['module_id' => 'authentication_internal', 'name' => 'defaultUserGroup']);
        $this->update('setting', ['name' => 'auth.defaultUserIdleTimeoutSec', 'module_id' => 'user'], ['module_id' => 'authentication_internal', 'name' => 'defaultUserIdleTimeoutSec']);
        $this->update('setting', ['name' => 'auth.allowGuestAccess', 'module_id' => 'user'], ['module_id' => 'authentication_internal', 'name' => 'allowGuestAccess']);
        $this->update('setting', ['name' => 'auth.defaultUserProfileVisibility', 'module_id' => 'user'], ['module_id' => 'authentication_internal', 'name' => 'defaultUserProfileVisibility']);

        // Fix authentication ldap module id
        $this->update('setting', ['name' => 'auth.ldap.enabled', 'module_id' => 'user'], ['module_id' => 'authentication_ldap', 'name' => 'enabled']);
        $this->update('setting', ['name' => 'auth.ldap.refreshUsers', 'module_id' => 'user'], ['module_id' => 'authentication_ldap', 'name' => 'refreshUsers']);
        $this->update('setting', ['name' => 'auth.ldap.username', 'module_id' => 'user'], ['module_id' => 'authentication_ldap', 'name' => 'username']);
        $this->update('setting', ['name' => 'auth.ldap.password', 'module_id' => 'user'], ['module_id' => 'authentication_ldap', 'name' => 'password']);
        $this->update('setting', ['name' => 'auth.ldap.hostname', 'module_id' => 'user'], ['module_id' => 'authentication_ldap', 'name' => 'hostname']);
        $this->update('setting', ['name' => 'auth.ldap.port', 'module_id' => 'user'], ['module_id' => 'authentication_ldap', 'name' => 'port']);
        $this->update('setting', ['name' => 'auth.ldap.encryption', 'module_id' => 'user'], ['module_id' => 'authentication_ldap', 'name' => 'encryption']);
        $this->update('setting', ['name' => 'auth.ldap.baseDn', 'module_id' => 'user'], ['module_id' => 'authentication_ldap', 'name' => 'baseDn']);
        $this->update('setting', ['name' => 'auth.ldap.loginFilter', 'module_id' => 'user'], ['module_id' => 'authentication_ldap', 'name' => 'loginFilter']);
        $this->update('setting', ['name' => 'auth.ldap.userFilter', 'module_id' => 'user'], ['module_id' => 'authentication_ldap', 'name' => 'userFilter']);
        $this->update('setting', ['name' => 'auth.ldap.usernameAttribute', 'module_id' => 'user'], ['module_id' => 'authentication_ldap', 'name' => 'usernameAttribute']);
        $this->update('setting', ['name' => 'auth.ldap.emailAttribute', 'module_id' => 'user'], ['module_id' => 'authentication_ldap', 'name' => 'emailAttribute']);

        // Fix cache settings module id
        $this->update('setting', ['name' => 'cache.class', 'module_id' => 'base'], ['module_id' => 'cache', 'name' => 'type']);
        $this->update('setting', ['name' => 'cache.expireTime', 'module_id' => 'base'], ['module_id' => 'cache', 'name' => 'expireTime']);

        // Fix mail settings module id
        $this->update('setting', ['name' => 'mailer.transportType', 'module_id' => 'base'], ['module_id' => 'mailing', 'name' => 'transportType']);
        $this->update('setting', ['name' => 'mailer.hostname', 'module_id' => 'base'], ['module_id' => 'mailing', 'name' => 'hostname']);
        $this->update('setting', ['name' => 'mailer.username', 'module_id' => 'base'], ['module_id' => 'mailing', 'name' => 'username']);
        $this->update('setting', ['name' => 'mailer.password', 'module_id' => 'base'], ['module_id' => 'mailing', 'name' => 'password']);
        $this->update('setting', ['name' => 'mailer.port', 'module_id' => 'base'], ['module_id' => 'mailing', 'name' => 'port']);
        $this->update('setting', ['name' => 'mailer.encryption', 'module_id' => 'base'], ['module_id' => 'mailing', 'name' => 'encryption']);
        $this->update('setting', ['name' => 'mailer.allowSelfSignedCerts', 'module_id' => 'base'], ['module_id' => 'mailing', 'name' => 'allowSelfSignedCerts']);
        $this->update('setting', ['name' => 'mailer.systemEmailAddress', 'module_id' => 'base'], ['module_id' => 'mailing', 'name' => 'systemEmailAddress']);
        $this->update('setting', ['name' => 'mailer.systemEmailName', 'module_id' => 'base'], ['module_id' => 'mailing', 'name' => 'systemEmailName']);
        $this->update('setting', ['name' => 'receive_email_activities', 'module_id' => 'activity'], ['name' => 'receive_email_activities']);
        $this->update('setting', ['name' => 'receive_email_notifications', 'module_id' => 'notification'], ['name' => 'receive_email_notifications']);

        // Fix proxy settings module id
        $this->update('setting', ['name' => 'proxy.enabled', 'module_id' => 'base'], ['module_id' => 'proxy', 'name' => 'enabled']);
        $this->update('setting', ['name' => 'proxy.server', 'module_id' => 'base'], ['module_id' => 'proxy', 'name' => 'server']);
        $this->update('setting', ['name' => 'proxy.port', 'module_id' => 'base'], ['module_id' => 'proxy', 'name' => 'port']);
        $this->update('setting', ['name' => 'proxy.user', 'module_id' => 'base'], ['module_id' => 'proxy', 'name' => 'user']);
        $this->update('setting', ['name' => 'proxy.password', 'module_id' => 'base'], ['module_id' => 'proxy', 'name' => 'password']);
        $this->update('setting', ['name' => 'proxy.noproxy', 'module_id' => 'base'], ['module_id' => 'proxy', 'name' => 'noproxy']);

        // fix user settings
        $this->update('contentcontainer_setting', ['module_id' => 'dashboard'], ['module_id' => 'share', 'name' => 'hideSharePanel']);
        $this->update('contentcontainer_setting', ['name' => 'receive_email_activities', 'module_id' => 'activity'], ['name' => 'receive_email_activities']);
        $this->update('contentcontainer_setting', ['name' => 'receive_email_notifications', 'module_id' => 'notification'], ['name' => 'receive_email_notifications']);
        $this->update('contentcontainer_setting', ['name' => 'enable_html5_desktop_notifications', 'module_id' => 'notification'], ['name' => 'receive_email_notifications']);
    }

    public function down()
    {
        echo "m160508_005740_settings_cleanup cannot be reverted.\n";

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
