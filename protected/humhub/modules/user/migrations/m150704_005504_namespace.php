<?php


use humhub\components\Migration;
use humhub\modules\user\models\User;


class m150704_005504_namespace extends Migration
{

    public function up()
    {

        $this->update('profile_field', ['field_type_class' => 'humhub\modules\user\models\fieldtype\Text'], ['field_type_class' => 'ProfileFieldTypeText']);
        $this->update('profile_field', ['field_type_class' => 'humhub\modules\user\models\fieldtype\Birthday'], ['field_type_class' => 'ProfileFieldTypeBirthday']);
        $this->update('profile_field', ['field_type_class' => 'humhub\modules\user\models\fieldtype\DateTime'], ['field_type_class' => 'ProfileFieldTypeDateTime']);
        $this->update('profile_field', ['field_type_class' => 'humhub\modules\user\models\fieldtype\Number'], ['field_type_class' => 'ProfileFieldTypeNumber']);
        $this->update('profile_field', ['field_type_class' => 'humhub\modules\user\models\fieldtype\Select'], ['field_type_class' => 'ProfileFieldTypeSelect']);
        $this->update('profile_field', ['field_type_class' => 'humhub\modules\user\models\fieldtype\TextArea'], ['field_type_class' => 'ProfileFieldTypeTextArea']);

        $this->renameClass('User', User::className());
        $this->renameClass('UserFollow', User::className());
    }

    public function down()
    {
        echo "m150704_005504_namespace cannot be reverted.\n";

        return false;
    }

    /*
      // Use safeUp/safeDown to run migration code within a transaction
      public function safeUp()
      {
      }

      public function safeDown()
      {
      }
     */
}
