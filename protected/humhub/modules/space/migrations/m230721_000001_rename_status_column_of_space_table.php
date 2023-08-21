<?php

use humhub\components\Migration;
use humhub\modules\space\models\Space;

/**
 * Handles adding columns to table `{{%space}}`.
 */
class m230721_000001_rename_status_column_of_space_table extends Migration
{
    protected string $table;

    public function __construct($config = [])
    {
        $this->table = Space::tableName();
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn($this->table, 'status', 'state');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo __CLASS__ . " cannot be reverted.\n";
    }
}
