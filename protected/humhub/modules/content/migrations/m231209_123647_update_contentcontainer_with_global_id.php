<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Migration;
use yii\db\Query;

class m231209_123647_update_contentcontainer_with_global_id extends Migration
{
    // protected properties
    protected string $table = 'contentcontainer';

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

        /**
         * Make sure, the table schema is refreshed.
         */
        $this->db->getTableSchema($this->table, true);

        $time = !$this->compact ? $this->beginCommand("creating GlobalId entries for {$this->table}s") : null;

        foreach ((new Query())->select("*")->from($this->table)->where(['gid' => 0])->all() as $row) {
            $guid = $row['guid'];

            $globalId = $this->createGlobalIdRecord($guid, [], $row['class'], '_CORE_');

            $this->updateSilent($this->table, ['gid' => $globalId, 'guid' => $guid], ['id' => $row['id']]);
        }

        if ($time) {
            $this->endCommand($time);
        }


        $this->safeCreateIndex(
            "ux-$this->table-gid",
            $this->table,
            'gid',
            true
        );

//        $this->safeDropPrimaryKey('PRIMARY', $this->table);
//        $this->safeAddPrimaryKey("pk-$this->table", $this->table, 'gid');

        $this->safeAddForeignKeyToGlobalIdTable();
    }
}
