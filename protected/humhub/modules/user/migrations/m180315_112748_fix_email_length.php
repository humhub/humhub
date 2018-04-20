<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use yii\db\Migration;

class m180315_112748_fix_email_length extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('user', 'email', $this->char(150)->null());
        $this->alterColumn('user_invite', 'email', $this->char(150)->notNull());
    }

    public function safeDown()
    {
        echo "m180315_112748_fix_email_length cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180315_112748_fix_email_length cannot be reverted.\n";

        return false;
    }
    */
}
