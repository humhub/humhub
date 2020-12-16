<?php

use humhub\modules\content\models\Content;
use humhub\modules\space\models\Space;
use yii\db\Migration;

/**
 * Class m201216_173118_fix_public_content_private_space
 */
class m201216_173119_fix_public_content_private_space extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $query = Content::find();
        $query->innerJoin('space', 'space.contentcontainer_id = content.contentcontainer_id');
        $query->andWhere('space.visibility = :visibility', [':visibility' => Space::VISIBILITY_NONE]);

        foreach ($query->all() as $contact) {
            $contact->updateAttributes(['visibility' => Content::VISIBILITY_PRIVATE]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201216_173118_fix_public_content_private_space cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201216_173118_fix_public_content_private_space cannot be reverted.\n";

        return false;
    }
    */
}
