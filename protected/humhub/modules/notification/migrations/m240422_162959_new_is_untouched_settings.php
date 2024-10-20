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
                'cs1.module_id',
                'cs1.contentcontainer_id',
                new Expression('"is_touched_settings" as name'),
                new Expression('"1" as value'),
            ])
            ->from(ContentContainerSetting::tableName() . ' AS cs1')
            ->leftJoin(
                ContentContainerSetting::tableName() . ' AS cs2',
                'cs1.module_id = cs2.module_id AND
                    cs1.contentcontainer_id = cs2.contentcontainer_id AND
                    cs2.name = "is_touched_settings"',
            )
            ->where([
                'cs1.name' => 'notification.like_email',
                'cs1.module_id' => 'notification',
            ])
            ->andWhere(['IS', 'cs2.id', new Expression('NULL')])
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
