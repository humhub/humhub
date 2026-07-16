<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

use humhub\components\Migration;
use humhub\modules\file\models\File;

/**
 * Add `public` and `standalone` flags to the file table
 */
class m260716_100000_file_add_public_standalone_columns extends Migration
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
        $this->safeAddColumn(
            $this->table,
            'public',
            $this->boolean()
                ->defaultValue(false)
                ->notNull()
                ->after('state'),
        );

        $this->safeAddColumn(
            $this->table,
            'standalone',
            $this->boolean()
                ->defaultValue(false)
                ->notNull()
                ->after('public'),
        );
    }
}
