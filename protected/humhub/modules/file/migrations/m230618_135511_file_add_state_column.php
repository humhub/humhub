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
class m230618_135511_file_add_state_column extends Migration
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
    public function safeUp()
    {
        $this->addColumn(
            $this->table,
            'state',
            $this->tinyInteger()
                ->defaultValue(File::STATE_PUBLISHED)
                ->notNull()
                ->after('guid'),
        );
    }
}
