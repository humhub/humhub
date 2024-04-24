<?php

use humhub\modules\content\models\ContentContainerSetting;
use yii\db\Expression;
use yii\db\Migration;
use yii\db\Query;

/**
 * Class m240422_162959_new_is_untouched_settings
 */
class m240422_162959_new_is_untouched_settings extends Migration
{
    /**
     * Inserts new rows into the `content_container_setting` table to add the "is_touched_settings"
     * setting for the "notification" module where the "name" is "notification.like_email" which was
     * previously used to know if the setting was modified by the user or not
     *
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $rows = (new Query())
            ->select([
                "module_id",
                "contentcontainer_id",
                new Expression("'is_touched_settings' as name"),
                new Expression("'1' as value"),
            ])
            ->from(ContentContainerSetting::tableName())
            ->where([
                'name' => 'notification.like_email',
                'module_id' => 'notification',
            ])
            ->all();

        $query = Yii::$app->db->createCommand()
            ->batchInsert(
                ContentContainerSetting::tableName(),
                ['module_id', 'contentcontainer_id', 'name', 'value'],
                $rows,
            );

        $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240422_162959_new_is_untouched_settings cannot be reverted.\n";

        return false;
    }
}
