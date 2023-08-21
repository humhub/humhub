<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

use humhub\components\Migration;
use humhub\modules\user\models\User;

/**
 * Handles adding columns to table `{{%space}}`.
 */
class m230721_000003_rename_status_column_of_user_table extends Migration
{
    protected string $table;

    public function __construct($config = [])
    {
        $this->table = User::tableName();
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
