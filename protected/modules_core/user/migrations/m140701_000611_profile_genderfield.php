<?php

class m140701_000611_profile_genderfield extends EDbMigration
{

    public function up()
    {

        // Check if the installer already ran when not create new profile field
        // (Typically the installer creates initial data.)
        if (HSetting::isInstalled()) {

            $db = $this->getDbConnection();

            // Get "General" Category Group Id
            $categoryId = $db->createCommand()
                    ->select('id')
                    ->from('profile_field_category')
                    ->where('title=:title', array(':title' => 'General'))
                    ->queryScalar();

            // Check if we got a category Id
            if ($categoryId == "") {
                throw new CException("Could not find 'General' profile field category!");
            }

            // Create manually profile field
            $insertCommand = $db->commandBuilder->createInsertCommand('profile_field', array(
                'profile_field_category_id' => $categoryId,
                'field_type_class' => 'ProfileFieldTypeSelect',
                'field_type_config' => '{"options":"male=>Male\r\nfemale=>Female\r\ncustom=>Custom"}',
                'internal_name' => 'gender',
                'title' => 'Gender',
                'sort_order' => '350',
                'editable' => '1',
                'visible' => '1',
                'show_at_registration' => '0',
                'required' => '0',
            ));
            $insertCommand->execute();

            // Create column for profile field
            $this->addColumn('profile', 'gender', 'varchar(255) DEFAULT NULL');
        }
    }

    public function down()
    {
        echo "m140701_000611_profile_genderfield does not support migration down.\n";
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
