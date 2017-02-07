<?php

use yii\db\Migration;
use yii\db\Expression;

/**
 * - Removes space_id and user_id column from content table and replaces it
 * with contentcontainer_id column.
 * 
 * - Adds foreign keys
 */
class m160220_013525_contentcontainer_id extends Migration
{

    public function up()
    {
        // Add contentcontainer_id to content table
        $this->addColumn('content', 'contentcontainer_id', $this->integer());

        // Set content container for space content
        $this->update('content', [
            'contentcontainer_id' => new Expression('(SELECT id FROM contentcontainer WHERE class=:spaceModel AND pk=space_id)', [':spaceModel' => \humhub\modules\space\models\Space::className()])
                ], ['IS NOT', 'space_id', new Expression('NULL')]
        );

        // Set content container for user content
        $this->update('content', [
            'contentcontainer_id' => new Expression('(SELECT id FROM contentcontainer WHERE class=:userModel AND pk=user_id)', [':userModel' => \humhub\modules\user\models\User::className()])
                ], ['IS', 'space_id', new Expression('NULL')]
        );

        // Ensure created_by is set to user_id
        $this->update('content', ['created_by' => new Expression('user_id')]);

        // Ensure updated_by is set
        $this->update('content', ['updated_by' => new Expression('created_by')], ['IS', 'updated_by', new Expression('NULL')]);

        // Make sure fk dont fail
        Yii::$app->db->createCommand('UPDATE content LEFT JOIN user ON content.updated_by = user.id SET content.updated_by = NULL WHERE user.id IS NULL')->execute();
        Yii::$app->db->createCommand('UPDATE content LEFT JOIN user ON content.created_by = user.id SET content.created_by = NULL WHERE user.id IS NULL')->execute();

        // Add FKs
        $this->addForeignKey('fk-contentcontainer', 'content', 'contentcontainer_id', 'contentcontainer', 'id', 'SET NULL');
        $this->addForeignKey('fk-create-user', 'content', 'created_by', 'user', 'id', 'SET NULL');
        $this->addForeignKey('fk-update-user', 'content', 'updated_by', 'user', 'id', 'SET NULL');


        $this->dropColumn('content', 'space_id');

        try {
            $this->dropForeignKey('fk_content-user_id', 'content');
        } catch (Exception $ex) {
            
        }
        $this->dropColumn('content', 'user_id');
    }

    public function down()
    {

        echo "m160220_013525_contentcontainer_id cannot be reverted.\n";

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
