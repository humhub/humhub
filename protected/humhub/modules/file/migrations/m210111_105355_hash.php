<?php

use yii\db\Migration;

/**
 * Class m210111_105355_hash
 */
class m210111_105355_hash extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('file', 'hash_sha1', $this->string(32)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('file', 'hash_sha1');
    }
}
