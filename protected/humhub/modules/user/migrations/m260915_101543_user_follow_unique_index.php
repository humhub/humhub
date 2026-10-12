<?php

use humhub\components\Migration;
use humhub\modules\user\models\Follow;

/**
 * Adds a unique index on (object_model, object_id, user_id) to the `user_follow`
 * table to prevent duplicate follow records being created by concurrent requests
 * (race condition / check-then-act in Followable::follow()).
 *
 * Mirrors the equivalent fix already applied to the `like` table in
 * humhub\modules\like\migrations\m240425_144905_unique_index.
 *
 * Class m260915_101543_user_follow_unique_index
 */
class m260915_101543_user_follow_unique_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Remove duplicate follow records, keeping the oldest (lowest id) per
        // (object_model, object_id, user_id) combination.
        $duplicates = Follow::find()
            ->groupBy(['object_model', 'object_id', 'user_id'])
            ->having('COUNT(*) > 1');

        foreach ($duplicates->each() as $follow) {
            /* @var Follow $follow */
            Follow::deleteAll(['AND',
                ['object_model' => $follow->object_model],
                ['object_id' => $follow->object_id],
                ['user_id' => $follow->user_id],
                ['!=', 'id', $follow->id],
            ]);
        }

        $this->safeCreateIndex('unique-object-user', 'user_follow', ['object_model', 'object_id', 'user_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260915_101543_user_follow_unique_index cannot be reverted.\n";

        return false;
    }
}
