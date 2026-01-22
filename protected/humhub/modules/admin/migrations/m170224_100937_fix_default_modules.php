<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.org/licences
 */

use humhub\components\Migration;
use yii\db\Expression;

class m170224_100937_fix_default_modules extends Migration
{
    public function up()
    {
        $this->safeAlterColumn('space_module', 'space_id', $this->integer()->null());
        $this->safeAlterColumn('user_module', 'user_id', $this->integer()->null());

        $this->update('space_module', ['space_id' => new Expression('NULL')], ['space_id' => 0]);
        $this->update('user_module', ['user_id' => new Expression('NULL')], ['user_id' => 0]);

        // TODO: All all to null
    }

    public function down()
    {
        echo "m170224_100937_fix_default_modules cannot be reverted.\n";

        return false;
    }
}
