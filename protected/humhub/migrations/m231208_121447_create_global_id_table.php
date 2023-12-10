<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Migration;

/**
 * Handles the creation of table `{{%class_map}}`.
 */
class m231208_121447_create_global_id_table extends Migration
{
    // protected properties
    protected string $table = 'global_id';

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%global_id}}');
    }


    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->safeCreateTable(
            $this->table,
            [
                'gid' => $this->primaryKey(11)->unsigned(),
                'guid' => $this->char(36)->notNull(),
                'class_map_id' => $this->integer(11)->unsigned()->notNull(),
                'state' => $this->integer(9),
                'url_slug' => $this->string(200),
            ]
        );

        /**
         * Make sure, the table schema is refreshed.
         *
         * @see static::renameClass()
         */
        $this->db->getTableSchema($this->table, true);

        $this->safeCreateIndex(
            "ux-$this->table-guid",
            $this->table,
            'guid',
            true
        );

        $this->safeCreateIndex(
            "ix-$this->table-class_map",
            $this->table,
            [
                'class_map_id',
                'state',
            ]
        );

        $this->safeCreateIndex(
            "ix-$this->table-state",
            $this->table,
            [
                'state',
            ]
        );

        $this->safeAddForeignKey(
            "fk-$this->table-class_map_id",
            $this->table,
            'class_map_id',
            'class_map',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
}
