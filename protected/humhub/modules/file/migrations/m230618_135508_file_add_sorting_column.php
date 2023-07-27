<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

use humhub\components\Migration;
use humhub\modules\file\models\File;
use yii\db\ActiveQuery;
use yii\db\Expression;

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
     * @throws \yii\base\ErrorException
     * @throws JsonException
     * @throws \yii\db\Exception
     */
    public function safeUp(): void
    {
        $this->safeAddColumn(
            $this->table,
            'sort_order',
            $this->integer(11)
                 ->after('object_id')
        );

        $command = Yii::$app->getDb()
                            ->createCommand()
        ;

        $command->update(
            $this->table,
            ['sort_order' => new Expression('id')]
        )
                ->execute()
        ;

        $query = Yii::createObject(ActiveQuery::className(), [File::class]);
        array_map(
            static function (
                File $file
            ) {
                $file->sort_order = $file->id;

                if (!$file->save()) {
                    throw new \yii\base\ErrorException(
                        "File $file->id could not be saved: "
                        . json_encode($file->getErrors(), JSON_THROW_ON_ERROR)
                    );
                }
            },
            $query->all()
        );

        $this->alterColumn(
            $this->table,
            'sort_order',
            $this->integer(11)
                 ->notNull()
        );

        $command->update(
            $this->table,
            ['object_model' => null],
            ['object_model' => '']
        )
                ->execute()
        ;

        $command->update(
            $this->table,
            ['object_id' => null],
            ['object_id' => '']
        )
                ->execute()
        ;

        $this->safeCreateIndex("ix-$this->table-object", $this->table, ['object_model', 'object_id', 'sort_order']);

        $this->safeDropIndex('index_object', $this->table);
    }
}
