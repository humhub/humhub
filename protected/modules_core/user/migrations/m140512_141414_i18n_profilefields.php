<?php

class m140512_141414_i18n_profilefields extends EDbMigration
{

    public function up()
    {
                $this->addColumn('profile_field', 'translation_category', 'varchar(255) DEFAULT NULL');
                $this->addColumn('profile_field', 'is_system', 'int(1) DEFAULT NULL');
                $this->addColumn('profile_field_category', 'translation_category', 'varchar(255) DEFAULT NULL');

                $this->update('profile_field', array(
                    'is_system' => 1
                ), 'internal_name="firstname" OR internal_name="lastname" OR internal_name="title"');
                
    }

    public function down()
    {
        echo "m140512_141414_i18n_profilefields does not support migration down.\n";
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
