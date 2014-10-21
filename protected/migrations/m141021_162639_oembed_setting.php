<?php

class m141021_162639_oembed_setting extends EDbMigration
{

    public function up()
    {
        $this->insert('setting', array(
            'name' => 'oembedProviders',
            'value_text' => '{"vimeo.com":"http:\/\/vimeo.com\/api\/oembed.json?scheme=https&url=%url%&format=json&maxwidth=450","youtube.com":"http:\/\/www.youtube.com\/oembed?scheme=https&url=%url%&format=json&maxwidth=450","youtu.be":"http:\/\/www.youtube.com\/oembed?scheme=https&url=%url%&format=json&maxwidth=450","soundcloud.com":"https:\/\/soundcloud.com\/oembed?url=%url%&format=json&maxwidth=450","slideshare.net":"https:\/\/www.slideshare.net\/api\/oembed\/2?url=%url%&format=json&maxwidth=450"}'
        ));
    }

    public function down()
    {
        echo "m141021_162639_oembed_setting does not support migration down.\n";
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
