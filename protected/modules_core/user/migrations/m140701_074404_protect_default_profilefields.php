<?php

class m140701_074404_protect_default_profilefields extends EDbMigration
{

    public function up()
    {

        $this->addColumn('profile_field_category', 'is_system', 'int(1) DEFAULT NULL');

        $this->update('profile_field', array(
            'is_system' => 1
                ), 'internal_name="gender" OR internal_name="street" OR internal_name="zip" OR internal_name="city" OR internal_name="country"');
        $this->update('profile_field', array(
            'is_system' => 1
                ), 'internal_name="state" OR internal_name="about" OR internal_name="phone_private" OR internal_name="phone_work" OR internal_name="mobile"');
        $this->update('profile_field', array(
            'is_system' => 1
                ), 'internal_name="mobile" OR internal_name="fax" OR internal_name="im_skype" OR internal_name="im_msn" OR internal_name="im_icq"');
        $this->update('profile_field', array(
            'is_system' => 1
                ), 'internal_name="im_xmpp" OR internal_name="url" OR internal_name="url_facebook" OR internal_name="url_linkedin" OR internal_name="url_xing"');
        $this->update('profile_field', array(
            'is_system' => 1
                ), 'internal_name="url_youtube" OR internal_name="url_vimeo" OR internal_name="url_flickr" OR internal_name="url_myspace" OR internal_name="url_googleplus" OR internal_name="url_twitter"');

        $this->update('profile_field', array(
            'is_system' => 1
                ), 'title="General" OR title="Communication" OR title="Social bookmarks"');
    }

    public function down()
    {
        echo "m140701_074404_protect_default_profilefields does not support migration down.\n";
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
