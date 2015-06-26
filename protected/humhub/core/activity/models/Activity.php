<?php

namespace humhub\core\activity\models;

use Yii;
use yii\web\HttpException;

/**
 * This is the model class for table "activity".
 *
 * @property integer $id
 * @property string $type
 * @property string $module
 * @property string $object_model
 * @property string $object_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Activity extends \humhub\core\content\components\activerecords\Content
{

    public $autoAddToWall = false;

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors()
    {
        return [
            [
                'class' => \humhub\components\behaviors\UnderlyingObject::className(),
                'mustBeInstanceOf' => [
                    \humhub\core\content\components\activerecords\Content::className(),
                    \humhub\core\content\components\activerecords\ContentContainer::className(),
                    \humhub\core\content\components\activerecords\ContentAddon::className(),
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by', 'object_id'], 'integer'],
            [['type'], 'string', 'max' => 45],
            [['module', 'object_model'], 'string', 'max' => 100]
        ];
    }

    /**
     * Creates an instance of Activity for given object.
     *
     * The activity instance inherits options/attributes like user, space or 
     * visibility of the given object.
     * 
     * Dont forget to also set activity attributes like type, module before
     * saving it!
     * 
     * @param Mixed $content Instance of HActiveRecordContent or HActiveRecordContentAddon
     * @return Activity Prepared activity for given $object
     */
    public static function CreateForContent($object)
    {
        $activity = new self;

        if (!$object instanceof \humhub\core\content\components\activerecords\Content && !$object instanceof \humhub\core\content\components\activerecords\ContentAddon) {
            throw new HttpException(500, Yii::t('ActivityModule.models_Activity', 'Could not create activity for this object type!'));
        }

        $content = $object->content;

        $activity->content->user_id = $content->created_by;

        // Always take visibilty of Content Object for that activity
        $activity->content->visibility = $content->visibility;

        // Auto Set object_model & object_id of given object
        $activity->object_model = $object::className();
        $activity->object_id = $object->getPrimaryKey();

        // Also assign space_id if set
        if ($content->container instanceof \humhub\core\space\models\Space) {
            $activity->content->space_id = $content->container->id;
        }

        return $activity;
    }

    /**
     * After Saving a new activity, the activity is automatically published
     * to the underlying workspace or user wall
     *
     */
    public function fire()
    {
        if ($this->content->container instanceof \humhub\core\space\models\Space) {
            // Post this activity to space wall
            $this->content->addToWall($this->content->container->wall_id);
        } elseif ($this->content->container instanceof \humhub\core\user\models\User) {
            // Post this activity to users wall
            if (isset(Yii::$app->user)) {
                $this->content->addToWall(Yii::$app->user->getIdentity()->wall_id);
            } else {
                // Maybe Console Script without Yii::app()->user
                $user = User::findOne(['id' => $this->created_by]);
                $this->content->addToWall($user->wall_id);
            }
        }
    }

    /**
     * Gets the Wall Output
     */
    public function getWallOut()
    {
        return \humhub\core\activity\widgets\Activity::widget(['activity' => $this]);
    }

    /**
     * Returns Mail Output for that activity
     *
     * @return type
     */
    public function getMailOut()
    {
        $controller = new Controller('MailX');

        // Determine View
        $view = 'application.modules_core.activity.views.activities.' . $this->type . "_mail";

        if ($this->module != "") {
            $view = 'application.modules_core.' . $this->module . '.views.activities.' . $this->type . "_mail";
            $viewPath = Yii::getPathOfAlias($view) . '.php';

            // Seems not exists, try 3rd party module folder
            if (!file_exists($viewPath)) {
                $view = 'application.modules.' . $this->module . '.views.activities.' . $this->type . "_mail";
            }
        }

        $viewPath = Yii::getPathOfAlias($view) . '.php';

        $underlyingObject = $this->getUnderlyingObject();

        $workspace = null;
        if ($this->content->space_id != "") {
            $workspace = Space::model()->findByPk($this->content->space_id);
        }

        $user = $this->content->user;
        if ($user == null)
            return;

        return $controller->renderInternal($viewPath, array(
                    'activity' => $this,
                    'wallEntryId' => 0,
                    'user' => $user,
                    'target' => $underlyingObject,
                    'workspace' => $workspace
                        ), true
        );
    }

}
