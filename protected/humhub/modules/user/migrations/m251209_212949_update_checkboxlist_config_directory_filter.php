<?php

use humhub\components\Migration;
use humhub\modules\user\models\fieldtype\CheckboxList;
use humhub\modules\user\models\ProfileField;
use yii\helpers\Json;

class m251209_212949_update_checkboxlist_config_directory_filter extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Update Checkbox List field Type config to allow being used in Directory Filters
        $fields = ProfileField::find()
            ->where(['field_type_class' => CheckboxList::class]);

        foreach ($fields->each() as $field) {
            $config = Json::decode($field->field_type_config);
            $config['canBeDirectoryFilter'] = true;
            $field->field_type_config = Json::encode($config);
            $field->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251209_212949_update_checkboxlist_config_directory_filter cannot be reverted.\n";

        return false;
    }
}
