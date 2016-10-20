<?php


use yii\db\Migration;

class m140703_104527_profile_birthdayfield extends Migration
{

    public function up()
    {
        // Check if the installer already ran when not create new profile field
        // (Typically the installer creates initial data.)
        if (\humhub\models\Setting::isInstalled()) {


            $row = (new \yii\db\Query())
                    ->select("*")
                    ->from('profile_field_category')
                    ->where(['title' => 'General'])
                    ->one();

            $categoryId = $row['id'];
            if ($categoryId == "") {
                throw new yii\base\Exception("Could not find 'General' profile field category!");
            }

            $this->insert('profile_field', [
                'profile_field_category_id' => $categoryId,
                'field_type_class' => 'ProfileFieldTypeBirthday',
                'field_type_config' => '',
                'internal_name' => 'birthday',
                'title' => 'Birthday',
                'sort_order' => '850',
                'editable' => '1',
                'is_system' => '1',
                'visible' => '1',
                'show_at_registration' => '0',
                'required' => '0',
            ]);

            // Create columns for profile field
            $this->addColumn('profile', 'birthday', 'DATETIME DEFAULT NULL');
            $this->addColumn('profile', 'birthday_hide_year', 'INT(1) DEFAULT NULL');
        }
    }

    public function down()
    {
        echo "m140703_104527_profile_birthdayfield does not support migration down.\n";
        return false;
    }

    /*
      // Use safeUp/safeDown to do migration with transaction
      public function safeUp()
      {
      }

      public function safeDown()
      {
      }
     */
}
