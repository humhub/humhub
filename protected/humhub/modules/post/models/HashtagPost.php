<?php

namespace humhub\modules\post\models;

use Yii;

/**
 * This is the model class for table "hashtag_post".
 *
 * @property integer $id
 * @property string $tag
 * @property integer $post_id
 *
 * @property Post $post
 */
class HashtagPost extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'hashtag_post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id'], 'integer'],
            [['tag'], 'string', 'max' => 250],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::className(), 'targetAttribute' => ['post_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tag' => 'Tag',
            'post_id' => 'Post ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::className(), ['id' => 'post_id']);
    }

    /**
     * Adds new tag-post relation
     *
     * @param Post $post
     */
    public static function addTag($post)
    {
        $post_id = $post->id;
        preg_match_all("/(#\w+)/u", $post->message, $matches);
        $hash_tags = array_map("unserialize", array_unique(array_map("serialize", $matches)));
        if (isset($hash_tags[0])) {
            foreach ($hash_tags[0] as $tag) {
                $htp = new HashtagPost();
                $htp->tag = $tag;
                $htp->post_id = $post_id;
                $htp->save();
            }
        }
    }
}
