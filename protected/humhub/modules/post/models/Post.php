<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\models;

use Yii;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\search\interfaces\Searchable;
use humhub\modules\user\models\User;

/**
 * This is the model class for table "post".
 *
 * @property integer $id
 * @property string $message_2trash
 * @property string $message
 * @property string $url
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Post extends ContentActiveRecord implements Searchable
{

    /**
     * @inheritdoc
     */
    public $wallEntryClass = 'humhub\modules\post\widgets\WallEntry';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message'], 'required'],
            [['message'], 'string'],
            [['url'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // Prebuild Previews for URLs in Message
        \humhub\models\UrlOembed::preload($this->message);

        // Check if Post Contains an Url
        if (preg_match('/http(.*?)(\s|$)/i', $this->message)) {
            // Set Filter Flag
            $this->url = 1;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {

        parent::afterSave($insert, $changedAttributes);

        // Handle mentioned users
        \humhub\modules\user\models\Mentioning::parse($this, $this->message);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getContentName()
    {
        return Yii::t('PostModule.models_Post', 'post');
    }

    public function getLabels($result = [], $includeContentName = true)
    {
        return parent::getLabels($result, false);
    }

    /**
     * @inheritdoc
     */
    public function getContentDescription()
    {
        return $this->message;
    }

    /**
     * @inheritdoc
     */
    public function getSearchAttributes()
    {
        $attributes = array(
            'message' => $this->message,
            'url' => $this->url,
            'user' => $this->getPostAuthorName()
        );

        $this->trigger(self::EVENT_SEARCH_ADD, new \humhub\modules\search\events\SearchAddEvent($attributes));

        return $attributes;
    }

    /**
     * @return string
     */
    private function getPostAuthorName()
    {
        $user = User::findOne(['id' => $this->created_by]);

        if ($user !== null && $user->isActive()) {
            return $user->getDisplayName();
        }

        return '';
    }

}
