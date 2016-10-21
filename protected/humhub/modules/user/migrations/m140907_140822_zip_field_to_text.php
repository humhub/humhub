<?php


use yii\db\Migration;

class m140907_140822_zip_field_to_text extends Migration
{

    public function up()
    {
        if (\humhub\models\Setting::isInstalled()) {

            $this->alterColumn('profile', 'zip', 'VARCHAR(10) DEFAULT NULL');

            $this->update('profile_field', array(
                'field_type_class' => 'ProfileFieldTypeText',
                'field_type_config' => '{"minLength":null,"maxLength":10,"validator":null,"default":null,"regexp":null,"regexpErrorMessage":null}'
                    ), 'internal_name="zip"');
        }
    }

    public function down()
    {
        $this->alterColumn('profile', 'birthday', 'INT(11) DEFAULT NULL');

        $this->update('profile_field', array(
            'field_type_class' => 'ProfileFieldTypeNumber',
            'field_type_config' => '{"maxValue":null,"minValue":null}'
                ), 'internal_name="zip"');
    }

}
