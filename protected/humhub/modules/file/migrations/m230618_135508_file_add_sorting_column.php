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
class m230618_135508_file_add_sorting_column extends Migration
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
     * @throws \yii\db\Exception
     */
    public function safeUp(): void
    {
        $command = Yii::$app->getDb()
            ->createCommand();

        $this->safeAddColumn(
            $this->table,
            'sort_order',
            $this->integer(11)
                ->notNull()
                ->defaultValue(100)
                ->after('object_id'),
        );

        $command->update(
            $this->table,
            ['object_model' => null],
            ['object_model' => ''],
        )
            ->execute();

        $command->update(
            $this->table,
            ['object_id' => null],
            ['object_id' => ''],
        )
            ->execute();

        $this->safeCreateIndex("ix-$this->table-object", $this->table, ['object_model', 'object_id', 'sort_order']);

        $this->safeDropIndex('index_object', $this->table);
    }
}
