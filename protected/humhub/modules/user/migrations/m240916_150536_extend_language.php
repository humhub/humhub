<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Migration;

/**
 * Class m240916_150536_extend_language
 */
class m240916_150536_extend_language extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('user', 'language', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('user_invite', 'language', 'varchar(20) DEFAULT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240916_150536_extend_language cannot be reverted.\n";

        return false;
    }
}
