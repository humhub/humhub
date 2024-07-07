<?php

use humhub\components\Migration;
use humhub\modules\like\models\Like;

/**
 * Class m240425_144905_unique_index
 */
class m240425_144905_unique_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $likes = Like::find()
            ->groupBy(['object_model', 'object_id', 'created_by'])
            ->having('COUNT(*) > 1');

        foreach ($likes->each() as $like) {
            /* @var Like $like */
            Like::deleteAll(['AND',
                ['object_model' => $like->object_model],
                ['object_id' => $like->object_id],
                ['created_by' => $like->created_by],
                ['!=', 'id', $like->id],
            ]);

            $like->flushCache();
        }

        $this->safeCreateIndex('unique-object-user', 'like', ['object_model', 'object_id', 'created_by'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240425_144905_unique_index cannot be reverted.\n";

        return false;
    }
}
