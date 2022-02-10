<?php

use humhub\modules\user\models\ProfileField;
use yii\db\Migration;

/**
 * Class m220210_162925_update_profile_text_fields_to_autocomplete
 */
class m220210_162925_update_profile_text_fields_to_autocomplete extends Migration
{
    /**
     * @var string[]
     */
    public $profileFieldsToUpdate = ['title', 'street', 'zip', 'city', 'country', 'state'];

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $textAutocompleteClassName = get_class(new \humhub\modules\user\models\fieldtype\TextAutocomplete());

        foreach ($this->profileFieldsToUpdate as $field) {
            if (ProfileField::find()->where(['internal_name' => $field])->exists()) {
                $this->update('profile_field', ['field_type_class' => $textAutocompleteClassName], ['internal_name' => $field]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $textClassName = get_class(new \humhub\modules\user\models\fieldtype\Text());

        foreach ($this->profileFieldsToUpdate as $field) {
            if (ProfileField::find()->where(['internal_name' => $field])->exists()) {
                $this->update('profile_field', ['field_type_class' => $textClassName], ['internal_name' => $field]);
            }
        }
    }
}
