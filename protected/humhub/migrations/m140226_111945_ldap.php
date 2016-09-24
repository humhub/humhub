<?php

use yii\db\Schema;
use yii\db\Migration;

class m140226_111945_ldap extends Migration
{

    public function up()
    {
        $this->addColumn('profile_field', 'ldap_attribute', 'string');

        $this->addColumn('space', 'ldap_dn', 'string');
        $this->addColumn('group', 'ldap_dn', 'string');

        $this->update('profile_field', array('ldap_attribute' => 'givenName'), "internal_name='firstname'");
        $this->update('profile_field', array('ldap_attribute' => 'sn'), "internal_name='lastname'");
        $this->update('profile_field', array('ldap_attribute' => 'title'), "internal_name='title'");
    }

    public function down()
    {
        echo "m140226_111945_ldap does not support migration down.\n";
        return false;
    }

    /*
      // Use safeUp/safeDown to do migration with transaction
      public function safeUp()
      {
      }

      public function safeDown()
      {
      }
     */
}
