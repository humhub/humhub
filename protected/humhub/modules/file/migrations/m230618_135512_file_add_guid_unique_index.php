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
class m230618_135512_file_add_guid_unique_index extends Migration
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
        $this->safeCreateIndex("ux-$this->table-guid", $this->table, ['guid'], true);
    }
}
