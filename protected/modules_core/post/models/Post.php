<?php

/**
 * This is the model class for table "post".
 *
 * The followings are the available columns in table 'post':
 * @property integer $id
 * @property string $message
 * @property string $url
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @package humhub.modules_core.post.models
 * @since 0.5
 */
class Post extends HActiveRecordContent implements ISearchable
{

    public $autoAddToWall = true;
    public $wallEditRoute = '//post/post/edit';

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Post the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'post';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('message', 'required'),
            array('created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('url', 'length', 'max' => 255),
            array('message, created_at, updated_at', 'safe'),
        );
    }

    /**
     * Before Delete, remove LikeCount (Cache) of target object.
     * Remove activity
     */
    protected function beforeDelete()
    {

        Notification::remove('Post', $this->id);

        return parent::beforeDelete();
    }

    public function beforeSave()
    {

        // Prebuild Previews for URLs in Message
        UrlOembed::preload($this->message);
        
        // Check if Post Contains an Url
        if (preg_match('/http(.*?)(\s|$)/i', $this->message)) {
            // Set Filter Flag
            $this->url = 1;
        }

        return parent::beforeSave();
    }

    /**
     * Before Save Addons
     *
     * @return type
     */
    public function afterSave()
    {

        parent::afterSave();

        if ($this->isNewRecord) {
            $activity = Activity::CreateForContent($this);
            $activity->type = "PostCreated";
            $activity->module = "post";
            $activity->save();
            $activity->fire();
        }

        // Handle mentioned users
        UserMentioning::parse($this, $this->message);
        
        return true;
    }

    /**
     * Returns the Wall Output
     */
    public function getWallOut()
    {
        return Yii::app()->getController()->widget('application.modules_core.post.widgets.PostWidget', array('post' => $this), true);
    }

    /**
     * Returns an array of informations used by search subsystem.
     * Function is defined in interface ISearchable
     *
     * @return Array
     */
    public function getSearchAttributes()
    {

        $belongsToType = "";
        $belongsToGuid = "";
        $belongsToId = "";
        if ($this->content->space_id != "") {
            $belongsToType = "Space";
            $belongsToId = $this->content->space_id;
            $workspace = Space::model()->findByPk($belongsToId);
            if ($workspace != null)
                $belongsToGuid = $workspace->guid;
        } else if ($this->content->user_id != "") {
            $belongsToType = "User";
            $belongsToId = $this->content->user_id;
            $user = User::model()->findByPk($belongsToId);
            if ($user != null)
                $belongsToGuid = $user->guid;
        }

        return array(
            // Assignment
            'belongsToType' => $belongsToType,
            'belongsToId' => $belongsToId,
            'belongsToGuid' => $belongsToGuid,
            'model' => 'Post',
            'pk' => $this->id,
            'title' => "Post",
            'url' => Yii::app()->createUrl('post/post/show', array('id' => $this->id)),
            // Some Indexed fields
            'message' => $this->message,
            'url' => $this->url,
        );
    }

    /**
     * Returns the Search Result Output
     */
    public function getSearchResult()
    {
        return Yii::app()->getController()->widget('application.modules_core.post.widgets.PostSearchResultWidget', array('post' => $this), true);
    }

    /**
     * Returns a title/text which identifies this IContent.
     *
     * e.g. Post: foo bar 123...
     *
     * @return String
     */
    public function getContentTitle()
    {
        return Yii::t('PostModule.models_Post', 'Post') . " \"" . Helpers::truncateText($this->message, 60) . "\"";
    }

}
