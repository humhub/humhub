<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use Yii;
use yii\helpers\Url;
use yii\bootstrap\Html;
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
    protected $layoutWeb = "@notification/views/layouts/web.php";

    /**
     * Layout file for mail version
     *
     * @var string
     */
    protected $layoutMail = "@notification/views/layouts/mail.php";

    /**
     * Layout file for mail plaintext version
     *
     * @var string
     */
    protected $layoutMailPlaintext = "@notification/views/layouts/mail_plaintext.php";

    /**
     * @var boolean automatically mark notification as seen after click on it
     */
    public $markAsSeenOnClick = true;

    /**
     * @var int number of combined notifications
     */
    public $groupCount = 0;

    /**
     * @var string the group key
     */
    protected $_groupKey = null;

    /**
     * @inheritdoc
     */
    public function getViewParams($params = [])
    {
        $params['url'] = Url::to(['/notification/entry', 'id' => $this->record->id], true);
        $params['space'] = $this->space;
        $params['isNew'] = ($this->record->seen != 1);
        $params['asHtml'] = $this->getAsHtml();
        $params['asText'] = $this->getAsText();

        return parent::getViewParams($params);
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

        // Load group key
        if ($this->_groupKey === null) {
            $this->_groupKey = $this->getGroupKey();
        }

        if ($this->_groupKey !== '') {
            $notification->group_key = $this->getGroupKey();
        }

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
        if ($this->record->group_key != '') {
            // Ensure to update all grouped notifications
            Notification::updateAll([
                'seen' => 1
                    ], [
                'class' => $this->record->class,
                'user_id' => $this->record->user_id,
                'group_key' => $this->record->group_key
            ]);
        } else {
            $this->record->seen = 1;
            $this->record->save();
        }

        // Automatically mark similar notifications (same source) as seen
        $similarNotifications = Notification::find()
                ->where(['source_class' => $this->record->source_class, 'source_pk' => $this->record->source_pk, 'user_id' => $this->record->user_id])
                ->andWhere(['!=', 'seen', '1']);
        foreach ($similarNotifications->all() as $n) {
            $n->getClass()->markAsSeen();
        }
    }

    /**
     * Returns key to group notifications.
     * If empty notification grouping is disabled.
     * 
     * @return string the group key
     */
    public function getGroupKey()
    {
        return "";
    }

    /**
     * Should be overwritten by subclasses. This method provides a user friendly
     * title for the different notification types.
     * 
     * @return string e.g. New Like
     */
    public static function getTitle()
    {
        return null;
    }

    /**
     * Returns text version of this notification
     * 
     * @return string
     */
    public function getAsText()
    {
        $html = $this->getAsHtml();

        if ($html === null) {
            return null;
        }

        return strip_tags($html);
    }

    /**
     * Returns Html text of this notification, 
     * 
     * @return type
     */
    public function getAsHtml()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function render($mode = self::OUTPUT_WEB, $params = array())
    {
        // Set default notification view - when not specified
        if ($this->viewName === null) {
            $this->viewName = 'default';
            $this->viewPath = '@notification/views/notification';
        }

        return parent::render($mode, $params);
    }

    /**
     * Returns the combined display names of a grouped notification.
     * Examples:
     *      User A and User B
     *      User A and 5 others
     * 
     * @return string the display names
     */
    public function getGroupUserDisplayNames()
    {
        if ($this->groupCount > 2) {
            list($user) = $this->getGroupLastUsers(1);
            return Yii::t('NotificationModule.base', '{displayName} and {number} others', [
                        'displayName' => Html::tag('strong', Html::encode($user->displayName)),
                        'number' => $this->groupCount - 1
            ]);
        }

        list($user1, $user2) = $this->getGroupLastUsers(2);
        return Yii::t('NotificationModule.base', '{displayName} and {displayName2}', [
                    'displayName' => Html::tag('strong', Html::encode($user1->displayName)),
                    'displayName2' => Html::tag('strong', Html::encode($user2->displayName)),
        ]);
    }

    /**
     * Returns the last users of a grouped notification
     * 
     * @param int $limit users to return
     * @return User[] the number of user
     */
    public function getGroupLastUsers($limit = 2)
    {
        $users = [];

        $query = Notification::find()
                ->where([
                    'notification.user_id' => $this->record->user_id,
                    'notification.class' => $this->record->class,
                    'notification.group_key' => $this->record->group_key
                ])
                ->joinWith(['originator', 'originator.profile'])
                ->orderBy(['notification.created_at' => SORT_DESC])
                ->groupBy(['notification.originator_user_id'])
                ->andWhere(['IS NOT', 'user.id', new \yii\db\Expression('NULL')])
                ->limit($limit);

        foreach ($query->all() as $notification) {
            $users[] = $notification->originator;
        }

        return $users;
    }

}
