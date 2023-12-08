<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Migration;

/**
 * Handles the creation of table `{{%class_map}}`.
 *
 * @author Martin Rüegg
 */
class m230329_191747_create_module_class_map_table extends Migration
{
    // protected properties
    protected string $table = 'class_map';

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%class_map}}');
    }


    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->safeCreateTable(
            $this->table,
            [
                'id' => $this->primaryKey(11)->unsigned(),
                'class_name' => $this->string(3072 / 4 - 100)->notNull(),
                'module_id' => $this->string(100)->notNull(),
                'source_file' => $this->string(3072 / 4),
            ]
        );

        /**
         * Make sure, the table schema is refreshed.
         *
         * @see static::renameClass()
         */
        $this->db->getTableSchema('class_map', true);

        $this->safeCreateIndex(
            "ux-$this->table-class_name",
            $this->table,
            'class_name',
            true
        );

        $this->safeCreateIndex(
            "ix-$this->table-module-class_name",
            $this->table,
            [
                'module_id',
                'class_name',
            ]
        );

        $this->safeAddForeignKey(
            "fk-$this->table-module_id",
            $this->table,
            'module_id',
            'module_enabled',
            'module_id',
            'CASCADE',
            'CASCADE'
        );
    }
}
