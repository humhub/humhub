<?php

use humhub\components\Migration;
use humhub\modules\content\interfaces\ContentProvider;
use yii\db\Query;

class m260106_175102_like_record_map extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute(
            'INSERT IGNORE INTO record_map (`model`, `pk`) SELECT DISTINCT l.object_model, l.object_id FROM `like` l WHERE l.object_model IS NOT NULL AND l.object_model != "";',
        );

        $this->safeAddColumn('like', 'content_addon_record_id', $this->integer()->null()->after('content_id'));

        $this->execute(
            'UPDATE `like` l JOIN record_map rm ON rm.`model` = l.object_model AND rm.`pk` = l.object_id SET l.content_addon_record_id = rm.id WHERE l.content_addon_record_id IS NULL AND l.object_model IS NOT NULL AND l.object_model != "";',
        );


        // Copy the parent content_id from each liked content-addon into the
        // `like` row. Content addons store their parent content via their own
        // `content_id` column (see ContentAddonActiveRecord::getContent()), so
        // this can be done with one set-based UPDATE per addon model instead of
        // resolving each record individually – essential on large installations.
        $addonModels = (new Query())
            ->select('object_model')
            ->distinct()
            ->from(\humhub\modules\like\models\Like::tableName())
            ->where('content_id IS NULL AND content_addon_record_id IS NOT NULL AND object_model IS NOT NULL AND object_model != ""')
            ->column();

        foreach ($addonModels as $model) {
            if (!class_exists($model) || !is_subclass_of($model, ContentProvider::class)) {
                Yii::warning('Skipping like content_id migration for unknown model: ' . $model, 'like');
                continue;
            }

            $table = $model::tableName();

            if ($this->columnExists('content_id', $table)) {
                // Standard content addon: the parent content_id lives on the addon row.
                $this->execute(
                    'UPDATE `like` l
                     JOIN ' . $this->db->quoteTableName($table) . ' a ON a.id = l.object_id
                     SET l.content_id = a.content_id
                     WHERE l.content_id IS NULL
                       AND l.content_addon_record_id IS NOT NULL
                       AND l.object_model = :model
                       AND a.content_id IS NOT NULL',
                    ['model' => $model],
                );
                continue;
            }

            // Fallback for ContentProviders that resolve their content without a
            // content_id column. Rare – resolve those per record.
            foreach (
                (new Query())->select(['id', 'object_id'])->from(\humhub\modules\like\models\Like::tableName())
                    ->where(['content_id' => null, 'object_model' => $model])
                    ->andWhere('content_addon_record_id IS NOT NULL AND object_id IS NOT NULL')->each() as $row
            ) {
                $addon = $model::findOne(['id' => $row['object_id']]);
                if ($addon !== null && $addon->content !== null) {
                    $this->updateSilent('like', ['content_id' => $addon->content->id], ['id' => $row['id']]);
                }
            }
        }


        $this->safeAddForeignKey('fk_like_content_addon', 'like', 'content_addon_record_id', 'record_map', 'id', 'CASCADE', 'CASCADE');

        $this->dropIndex('index_object', 'like');
        $this->dropIndex('unique-object-user', 'like');
        $this->createIndex('idx_unique_user', 'like', ['content_id', 'content_addon_record_id', 'created_by'], true);

        $this->safeDropColumn('like', 'object_model');
        $this->safeDropColumn('like', 'object_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260106_175102_like_record_map cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260106_175102_like_record_map cannot be reverted.\n";

        return false;
    }
    */
}
