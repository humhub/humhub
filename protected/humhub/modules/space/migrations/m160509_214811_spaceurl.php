<?php

use yii\db\Query;
use humhub\components\Migration;

class m160509_214811_spaceurl extends Migration
{

    public function up()
    {
        if (!class_exists('URLify')) {
            throw new Exception('URLify class not found - please run composer update!');
        }

        $this->addColumn('space', 'url', $this->string(45));
        $this->createIndex('url-unique', 'space', 'url', true);

        $rows = (new Query())
                ->select("*")
                ->from('space')
                ->all();
        foreach ($rows as $row) {
            $url = \humhub\modules\space\components\UrlValidator::autogenerateUniqueSpaceUrl($row['name']);
            $this->updateSilent('space', ['url' => $url], ['id' => $row['id']]);
        }
    }

    public function down()
    {
        echo "m160509_214811_spaceurl cannot be reverted.\n";

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
