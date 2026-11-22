<?php

use humhub\components\Migration;

/**
 * The locks of WebDAV clients, in the schema of Sabre's PDO lock backend.
 *
 * `token` and `uri` are binary, as in Sabre's own schema: a lock on `Report.docx` must not
 * match `report.docx`, which a case-insensitive collation would.
 */
class m261121_120000_dav_initial extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeCreateTable('dav_lock', [
            'id' => $this->primaryKey(),
            'owner' => $this->string(100)->null(),
            'timeout' => $this->integer()->unsigned()->null(),
            'created' => $this->integer()->null(),
            'token' => 'VARBINARY(100) NULL',
            'scope' => $this->tinyInteger()->null(),
            'depth' => $this->tinyInteger()->null(),
            'uri' => 'VARBINARY(1000) NULL',
        ]);
        $this->safeCreateIndex('idx_dav_lock_token', 'dav_lock', 'token');
        $this->safeCreateIndex('idx_dav_lock_uri', 'dav_lock', 'uri(100)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropTable('dav_lock');
    }
}
