<?php

use humhub\modules\user\models\fieldtype\CheckboxList;
use humhub\modules\user\models\fieldtype\Select;
use humhub\modules\user\models\ProfileField;
use yii\db\Migration;
use yii\helpers\Json;

/**
 * Class m240423_170311_profile_checkbox_list_field
 */
class m240423_170311_profile_checkbox_list_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $fields = ProfileField::find()
            ->where(['IN', 'field_type_class', [CheckboxList::class, Select::class]]);

        foreach ($fields->all() as $field) {
            /* @var ProfileField $field */
            $fixedConfig = $this->getFixedProfileFieldConfig($field);
            if ($fixedConfig !== null) {
                $field->field_type_config = $fixedConfig;
                $field->save();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240423_170311_profile_checkbox_list_field cannot be reverted.\n";
        return false;
    }

    private function getFixedProfileFieldConfig(ProfileField $field): ?string
    {
        $config = Json::decode($field->field_type_config);

        if (!isset($config['options']) || $config['options'] === '') {
            return null;
        }

        $keyType = $field->field_type_class === Select::class ? 'index' : 'value';
        $fixedOptions = [];
        $index = 0;
        $fixed = false;
        foreach (preg_split('/[\r\n]+/', $config['options']) as $option) {
            if (strpos($option, '=>') === false) {
                // Fix an option without a Key
                $fixed = true;
                $fixedOptions[] = ($keyType === 'index' ? $index++ : trim($option)) . '=>' . $option;
            } else {
                // Leave a correct option as is
                $fixedOptions[] = $option;
            }
        }

        if ($fixed === false) {
            return null;
        }

        $config['options'] = implode("\r\n", $fixedOptions);

        return Json::encode($config);
    }
}
