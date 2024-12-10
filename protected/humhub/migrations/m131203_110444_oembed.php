<?php

use humhub\components\Migration;

class m131203_110444_oembed extends Migration
{
    public function up()
    {
        $this->safeCreateTable('url_oembed', [
            'url' => 'varchar(180) NOT NULL',
            'preview' => 'text NOT NULL',
            'PRIMARY KEY (`url`)',
        ]);

        $this->safeRenameColumn('post', 'message', 'message_2trash');
        $this->safeRenameColumn('post', 'original_message', 'message');
    }

    public function down()
    {
        echo "m131203_110444_oembed does not support migration down.\n";
        return false;
    }
}
