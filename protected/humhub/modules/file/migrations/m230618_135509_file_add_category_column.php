<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

use humhub\components\Migration;
use humhub\modules\file\models\File;

/**
 * Add and film GUID column
 */
class m230618_135509_file_add_category_column extends Migration
{
    // protected properties
    protected string $table;

    public function __construct($config = [])
    {
        $this->table = File::tableName();
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $schema = Yii::$app->getDb()->getTableSchema($this->table, true);
        $after = $schema->getColumn('state') ? 'state' : 'guid';

        $this->safeAddColumn(
            $this->table,
            'category',
            $this->integer(11)
                ->unsigned()
                ->notNull()
                ->defaultValue(0)
                ->after($after),
        );

        $this->safeCreateIndex("ix-$this->table-category", $this->table, ['category', 'object_model', 'object_id']);
    }
}
