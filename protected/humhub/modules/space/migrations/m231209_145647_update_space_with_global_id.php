<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Migration;
use yii\db\Query;

class m231209_145647_update_space_with_global_id extends Migration
{
    // protected properties
    protected string $table = 'space';

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo __CLASS__ . " cannot be reverted.\n";

        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->safeAddGidReferenceColumn($this->table);

        // add foreign key for table `contentcontainer`
        $this->safeAddForeignKey(
            sprintf("fk-%s-%s", $this->table, 'contentcontainer_id'),
            $this->table,
            'contentcontainer_id',
            'contentcontainer',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /**
         * Make sure, the table schema is refreshed.
         */
        $this->db->getTableSchema($this->table, true);

        $time = !$this->compact ? $this->beginCommand("creating GlobalId entries for {$this->table}s") : null;

        foreach ((new Query())->select("*")->from($this->table)->where(['gid' => 0])->all() as $row) {
            $guid = $row['guid'];

            $globalId = $this->createGlobalIdRecord(
                $guid,
                ['state' => $row['status'], 'url_slug' => $row['url']],
                'humhub\\modules\\space\\models\\Space',
                '_CORE_'
            );

            $this->updateSilent($this->table, ['gid' => $globalId, 'guid' => $guid], ['id' => $row['id']]);
        }

        if ($time) {
            $this->endCommand($time);
        }

        $this->safeCreateIndex(
            "ux-$this->table-igd",
            $this->table,
            'gid',
            true
        );

//        $this->safeDropPrimaryKey('PRIMARY', $this->table);
//        $this->safeAddPrimaryKey("pk-$this->table", $this->table, 'gid');

        $this->safeAddForeignKeyToGlobalIdTable();
    }
}
