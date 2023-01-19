<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.org/licences
 */

use yii\db\Migration;

class m140513_180317_createlogging extends Migration
{
    public function up()
    {
        $this->createTable('logging', [
            'id' => 'pk',
            'level' => 'varchar(128)',
            'category' => 'varchar(128)',
            'logtime' => 'integer',
            'message' => 'text',
        ]);
    }

    public function down()
    {
        echo "m140513_180317_createlogging does not support migration down.\n";

        return false;
    }
}
