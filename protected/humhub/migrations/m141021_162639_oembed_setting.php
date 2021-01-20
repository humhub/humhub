<?php


use yii\db\Migration;
use yii\helpers\Json;

class m141021_162639_oembed_setting extends Migration
{

    public function up()
    {
        $this->insert('setting', [
            'name' => 'oembedProviders',
            'value_text' => Json::encode([
                'vimeo.com'      => 'https://vimeo.com/api/oembed.json?scheme=https&url=%url%&format=json&maxwidth=450',
                'youtube.com'    => 'https://www.youtube.com/oembed?scheme=https&url=%url%&format=json&maxwidth=450',
                'youtu.be'       => 'https://www.youtube.com/oembed?scheme=https&url=%url%&format=json&maxwidth=450',
                'soundcloud.com' => 'https://soundcloud.com/oembed?url=%url%&format=json&maxwidth=450',
                'slideshare.net' => 'https://www.slideshare.net/api/oembed/2?url=%url%&format=json&maxwidth=450',
            ])
        ]);
    }

    public function down()
    {
        echo "m141021_162639_oembed_setting does not support migration down.\n";
        return false;
    }

}
