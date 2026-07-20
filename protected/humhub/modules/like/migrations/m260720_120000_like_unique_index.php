<?php

use humhub\components\Migration;

class m260720_120000_like_unique_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // idx_unique_user contains the nullable content_addon_record_id, so it
        // never applied to likes on the content itself (NULL values are distinct
        // in unique indexes). Enforce uniqueness over a generated column that
        // maps NULL to 0 instead.

        // Remove duplicate content likes created while the index did not apply
        $this->execute(
            'DELETE l1 FROM `like` l1
                 JOIN `like` l2 ON l1.content_id = l2.content_id AND l1.created_by = l2.created_by AND l1.id > l2.id
             WHERE l1.content_addon_record_id IS NULL AND l2.content_addon_record_id IS NULL',
        );

        if (!$this->columnExists('content_addon_record_key', 'like')) {
            // A generated column cannot be based on a column with an
            // ON UPDATE CASCADE foreign key; record_map ids never change, so the
            // foreign key is re-created with ON UPDATE RESTRICT below
            $this->safeDropForeignKey('fk_like_content_addon', 'like');

            // MariaDB (min version 10.1) only knows PERSISTENT, MySQL only STORED
            $keyword = stripos($this->db->getServerVersion(), 'mariadb') !== false ? 'PERSISTENT' : 'STORED';
            $this->execute(
                'ALTER TABLE `like` ADD COLUMN `content_addon_record_key` INT GENERATED ALWAYS AS (IFNULL(`content_addon_record_id`, 0)) ' . $keyword,
            );
        }

        $this->safeAddForeignKey('fk_like_content_addon', 'like', 'content_addon_record_id', 'record_map', 'id', 'CASCADE', 'RESTRICT');

        $this->safeCreateIndex('idx_unique_like', 'like', ['content_id', 'content_addon_record_key', 'created_by'], true);
        $this->safeDropIndex('idx_unique_user', 'like');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260720_120000_like_unique_index cannot be reverted.\n";

        return false;
    }
}
