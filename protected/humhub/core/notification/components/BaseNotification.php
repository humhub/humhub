<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\core\notification\components;

use Yii;
use yii\base\ViewContextInterface;
use ReflectionClass;
use humhub\core\notification\models\Notification;
use humhub\core\user\models\User;
use humhub\core\content\components\activerecords\Content;
use humhub\core\content\components\activerecords\ContentAddon;
use humhub\core\content\components\activerecords\ContentContainer;

/**
 * Description of BaseNotification
 *
 * @author luke
 */
class BaseNotification extends \yii\base\Component implements ViewContextInterface
{

    const OUTPUT_WEB = 'web';
    const OUTPUT_MAIL = 'mail';

    /**
     * User which created this notification.
     * 
     * @var \humhub\core\user\models\User
     */
    public $originator;

    /**
     * @var string
     */
    public $viewName = null;

    /**
     * Source of this notification 
     * As example this can be a Space, Like or Post.
     * 
     * @var \yii\db\ActiveRecord
     */
    public $source;

    /**
     * Space this notification belongs to. (Optional)
     * If source is a Content, ContentAddon or ContentContainer this will be
     * automatically set.
     * 
     * @var \humhub\core\space\models\Space
     */
    public $space;

    /**
     * Layout file for web version
     * 
     * @var string
     */
    protected $layoutWeb = "@humhub/core/notification/views/layouts/web.php";

    /**
     * Layout file for mail version
     * 
     * @var string
     */
    protected $layoutMail = "@humhub/core/notification/views/layouts/mail.php";

    /**
     * The notification record this notification belongs to
     * 
     * @var Notification
     */
    public $record;

    /**
     * Renders the notification
     * 
     * @return string
     */
    public function render($mode = self::OUTPUT_WEB, $params = [])
    {
        $params['originator'] = $this->originator;
        $params['source'] = $this->source;
        $params['space'] = $this->space;
        $params['record'] = $this->record;
        $params['isNew'] = $this->record->seen;
        $params['url'] = $this->getUrl();

        $viewFile = $this->getViewPath() . '/' . $this->viewName . '.php';

        // Switch to extra mail view file - if exists (otherwise use web view)
        if ($mode == self::OUTPUT_MAIL) {
            $viewMailFile = $this->getViewPath() . '/mail/' . $this->viewName . '.php';
            if (file_exists($viewMailFile)) {
                $viewFile = $viewMailFile;
            }
        }

        $params['content'] = Yii::$app->getView()->renderFile($viewFile, $params, $this);

        return Yii::$app->getView()->renderFile($this->layoutWeb, $params, $this);
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
        // Skip - do not set notification to the originator
        if ($this->originator !== null && $user->id == $this->originator->id) {
            return;
        }


        $notification = new Notification;
        $notification->user_id = $user->id;
        $notification->class = $this->className();

        if ($this->source !== null) {
            $notification->source_pk = $this->source->getPrimaryKey();
            $notification->source_class = $this->source->className();

            // Automatically set spaceId if source is Content/Addon/Container
            if ($this->source instanceof Content || $this->source instanceof ContentAddon) {
                if ($this->source->content->container instanceof \humhub\core\space\models\Space) {
                    $notification->space_id = $this->source->content->container->id;
                }
            } elseif ($this->source instanceof \humhub\core\space\models\Space) {
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
    public function delete(\humhub\core\user\models\User $user = null)
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
     * Returns the directory containing the view files for this notification.
     * The default implementation returns the 'views' subdirectory under the directory containing the notification class file.
     * @return string the directory containing the view files for this notification.
     */
    public function getViewPath()
    {
        $class = new ReflectionClass($this);
        return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * Url of the origin of this notification
     * If source is a Content / ContentAddon / ContentContainer this will automatically generated.
     *  
     * @return string
     */
    public function getUrl()
    {
        return "foourl";
    }

    /**
     * Build info text about a content
     * 
     * This is a combination a the type of the content with a short preview
     * of it.
     * 
     * @param Content $content
     * @return string
     */
    public function getContentInfo(\humhub\core\content\interfaces\ContentTitlePreview $content)
    {
        return \yii\helpers\Html::encode($content->getContentTitle()) .
                ' ' .
                \humhub\widgets\RichText::widget(['text' => $content->getContentPreview(), 'minimal' => true, 'maxLength' => 60]);
    }

}
