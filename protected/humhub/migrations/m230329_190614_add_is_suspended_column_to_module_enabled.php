<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Migration;
use humhub\models\ModuleEnabled;

/**
 * Class m230329_190613_add_disabled_column_to_module_enabled
 */
class m230329_190614_add_is_suspended_column_to_module_enabled extends Migration
{
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
        $this->safeAddColumn(
            'module_enabled',
            'is_suspended',
            $this->boolean()
                 ->notNull()
                 ->defaultValue(0)
        );

        $this->createIndex(
            'idx-is_suspended-module',
            'module_enabled',
            [
                'module_id',
                'is_suspended',
            ]
        );

        if (! ModuleEnabled::findOne([ "module_id" => ModuleEnabled::FAKE_CORE_MODULE_ID ])) {
            ( new ModuleEnabled([ "module_id" => ModuleEnabled::FAKE_CORE_MODULE_ID ]) )->save();
        }
    }
}
