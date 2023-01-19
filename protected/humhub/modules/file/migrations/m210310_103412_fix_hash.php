<?php

use yii\db\Migration;

/**
 * Class m210310_103412_fix_hash
 */
class m210310_103412_fix_hash extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('file', ['hash_sha1' => NULL]);
        $this->alterColumn('file', 'hash_sha1', $this->string(40)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210310_103412_fix_hash cannot be reverted.\n";

        return false;
    }
}
