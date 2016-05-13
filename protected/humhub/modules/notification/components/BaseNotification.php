<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;


use yii\helpers\Url;
use humhub\modules\notification\models\Notification;
use humhub\modules\user\models\User;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;

/**
 * BaseNotification
 *
 * @author luke
 */
abstract class BaseNotification extends \humhub\components\SocialActivity
{
    /**
     * Space this notification belongs to. (Optional)
     * If source is a Content, ContentAddon or ContentContainer this will be
     * automatically set.
     *
     * @var \humhub\modules\space\models\Space
     */
    public $space;

    /**
     * Layout file for web version
     *
     * @var string
     */
    protected $layoutWeb = "@humhub/modules/notification/views/layouts/web.php";

    /**
     * Layout file for mail version
     *
     * @var string
     */
    protected $layoutMail = "@humhub/modules/notification/views/layouts/mail.php";

	/**
     * Layout file for mail plaintext version
     *
     * @var string
     */
    protected $layoutMailPlaintext = "@humhub/modules/notification/views/layouts/mail_plaintext.php";

    /**
     * @var boolean automatically mark notification as seen after click on it
     */
    public $markAsSeenOnClick = true;
    
    /**
     * Renders the notification
     *
     * @return string
     */
    public function getViewParams()
    {
        return [
            'url' => Url::to(['/notification/entry', 'id' => $this->record->id], true),
            'space' => $this->space,
            'isNew' => ($this->record->seen != 1)
        ];
    }

    /**
     * Sends this notification to a set of users.
     *
     * @param mixed $users can be an array of User records or an ActiveQuery.
     */
    public function sendBulk($users)
    {
        if ($users instanceof \yii\db\ActiveQuery) {
            $users = $users->all();
        }

        foreach ($users as $user) {
            $this->send($user);
        }
    }

    /**
     * Sends this notification to a User
     *
     * @param User $user
     */
    public function send(User $user)
    {

        if ($this->moduleId == "") {
            throw new \yii\base\InvalidConfigException("No moduleId given!");
        }

        // Skip - do not set notification to the originator
        if ($this->originator !== null && $user->id == $this->originator->id) {
            return;
        }

        $notification = new Notification;
        $notification->user_id = $user->id;
        $notification->class = $this->className();
        $notification->module = $this->moduleId;
        $notification->seen = 0;

        if ($this->source !== null) {
            $notification->source_pk = $this->source->getPrimaryKey();
            $notification->source_class = $this->source->className();

            // Automatically set spaceId if source is Content/Addon/Container
            if ($this->source instanceof ContentActiveRecord || $this->source instanceof ContentAddonActiveRecord) {
                if ($this->source->content->container instanceof \humhub\modules\space\models\Space) {
                    $notification->space_id = $this->source->content->container->id;
                }
            } elseif ($this->source instanceof \humhub\modules\space\models\Space) {
                $notification->space_id = $this->source->id;
            }
        }

        if ($this->originator !== null) {
            $notification->originator_user_id = $this->originator->id;
        }

        $notification->save();
    }

    /**
     * Deletes this notification
     */
    public function delete(\humhub\modules\user\models\User $user = null)
    {
        $condition = [];

        $condition['class'] = $this->className();

        if ($user !== null) {
            $condition['user_id'] = $user->id;
        }

        if ($this->originator !== null) {
            $condition['originator_user_id'] = $this->originator->id;
        }

        if ($this->source !== null) {
            $condition['source_pk'] = $this->source->getPrimaryKey();
            $condition['source_class'] = $this->source->className();
        }

        Notification::deleteAll($condition);
    }

    /**
     * Marks notification as seen
     */
    public function markAsSeen()
    {
        $this->record->seen = 1;
        $this->record->save();
    }
    
     /**
     * Should be overwritten by subclasses. This method provides a user friendly
     * title for the different notification types.
     * @return type
     */
    public static function getTitle()
    {
        return null;
    }
    
}
