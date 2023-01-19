<?php

use humhub\modules\user\models\User;
use yii\db\Migration;

/**
 * Class m210727_102150_follow_friend
 */
class m210727_102150_follow_friend extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('INSERT INTO user_follow (object_model, object_id, user_id, send_notifications)
            SELECT ' . $this->db->quoteValue(User::class) . ', user_friendship.friend_user_id, user_friendship.user_id, 0 FROM user_friendship
            LEFT JOIN user_follow ON user_follow.object_id = user_friendship.friend_user_id
                                 AND user_follow.user_id = user_friendship.user_id
                                 AND user_follow.object_model = ' . $this->db->quoteValue(User::class) . '
            WHERE user_follow.object_id IS NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210727_102150_follow_friend cannot be reverted.\n";

        return false;
    }

}
