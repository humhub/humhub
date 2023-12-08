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
 *
 * @author Martin Rüegg
 */
class m230329_190614_add_is_paused_column_to_module_enabled extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230329_190613_add_disabled_column_to_module_enabled cannot be reverted.\n";

        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn(
            'module_enabled',
            'is_paused',
            $this->boolean()
                 ->notNull()
                 ->defaultValue(0)
        );

        $this->createIndex(
            'idx-is_paused-module',
            'module_enabled',
            [
                'module_id',
                'is_paused',
            ]
        );

        if (! ModuleEnabled::findOne([ "module_id" => ModuleEnabled::FAKE_CORE_MODULE_ID ])) {
            ( new ModuleEnabled([ "module_id" => ModuleEnabled::FAKE_CORE_MODULE_ID ]) )->save();
        }
    }
}
