<?php

use humhub\modules\user\models\fieldtype\CheckboxList;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\ProfileField;
use yii\db\Migration;

class m251215_120928_alter_checkboxlist_columns_to_profile_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach (ProfileField::findAll(['field_type_class' => CheckboxList::class]) as $profileField) {
            $this->alterColumn('profile', $profileField->internal_name, $this->text());
        }
        Yii::$app->getDb()->getSchema()->refreshTableSchema(Profile::tableName());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251215_120928_alter_checkboxlist_columns_to_profile_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251215_120928_alter_checkboxlist_columns_to_profile_table cannot be reverted.\n";

        return false;
    }
    */
}
