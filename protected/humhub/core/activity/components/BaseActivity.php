<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\core\activity\components;

use Yii;
use ReflectionClass;
use humhub\core\user\models\User;
use humhub\core\activity\models\Activity;
use humhub\core\content\components\activerecords\Content;
use humhub\core\content\components\activerecords\ContentAddon;
use humhub\core\content\components\activerecords\ContentContainer;

/**
 * Description of BaseActivity
 *
 * @author luke
 */
class BaseActivity extends \yii\base\Component
{

    const OUTPUT_WEB = 'web';
    const OUTPUT_MAIL = 'mail';

    /**
     * This can be a Content/ContentAddon or ContentContainer Object
     * 
     * @var mixed
     */
    public $source;

    /**
     * @var string the view file to show this activity
     */
    public $viewName = "";

    /**
     * @var string
     */
    public $moduleId = "";

    /**
     * @var int
     */
    public $visibility = null;

    /**
     * @var User
     */
    public $originator;

    /**
     * @var string the layout file for web view
     */
    protected $layoutWeb = "@humhub/core/activity/views/layouts/web.php";

    /**
     * @var string the layotu file for mail view
     */
    protected $layoutMail = "@humhub/core/activity/views/layouts/mail.php";

    /**
     * The actvity record this notification belongs to
     * 
     * @var Notification
     */
    public $record;

    /**
     * @var boolean
     */
    public $clickable = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->viewName == "") {
            throw new \yii\base\InvalidConfigException("Missing viewName!");
        }

        if ($this->visibility === null) {
            $this->visibility = \humhub\core\content\models\Content::VISIBILITY_PRIVATE;
        }

        parent::init();
    }

    /**
     * Creates an activity 
     * 
     * @throws \yii\base\Exception
     */
    public function create()
    {
        $model = new Activity;
        $model->type = $this->className();
        $model->object_model = $this->source->className();
        $model->object_id = $this->source->getPrimaryKey();

        if ($this->source instanceof Content || $this->source instanceof ContentAddon) {
            $model->content->container = $this->source->content->container;
            $model->content->visibility = $this->source->content->visibility;

            if ($this->source instanceof Content) {
                $model->content->user_id = $this->source->content->user_id;
            } else {
                $model->content->user_id = $this->source->created_by;
            }
        } elseif ($this->source instanceof ContentContainer) {
            $model->content->visibility = $this->visibility;
            $model->content->container = $this->source;

            if (Yii::$app->user->isGuest) {
                $model->content->created_by = $this->originator->id;
                $model->created_by = $this->originator->id;
            }
        } else {
            throw new \yii\base\Exception("Invalid source object type!");
        }

        if (!$model->validate() || !$model->save()) {
            throw new \yii\base\Exception("Could not save activity!" . print_r($model->getErrors(), 1));
        }
    }

    /**
     * Renders activity output
     * 
     * @param string $mode
     * @param array $params
     * @return string the output
     */
    public function render($mode = self::OUTPUT_WEB, $params = [])
    {
        $params['originator'] = $this->originator;
        $params['source'] = $this->source;
        $params['record'] = $this->record;
        $params['url'] = $this->getUrl();
        $params['clickable'] = $this->clickable;

        $viewFile = $this->getViewPath() . '/' . $this->viewName . '.php';

        // Switch to extra mail view file - if exists (otherwise use web view)
        if ($mode == self::OUTPUT_MAIL) {
            $viewMailFile = $this->getViewPath() . '/mail/' . $this->viewName . '.php';
            if (file_exists($viewMailFile)) {
                $viewFile = $viewMailFile;
            }
        }

        $params['content'] = Yii::$app->getView()->renderFile($viewFile, $params, $this);

        return Yii::$app->getView()->renderFile(($mode == self::OUTPUT_WEB) ? $this->layoutWeb : $this->layoutMail, $params, $this);
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
                ' "' .
                \humhub\widgets\RichText::widget(['text' => $content->getContentPreview(), 'minimal' => true, 'maxLength' => 60]) . '"';
    }

    /**
     * Url of the origin of this notification
     * If source is a Content / ContentAddon / ContentContainer this will automatically generated.
     *  
     * @return string
     */
    public function getUrl()
    {
        if ($this->source instanceof Content || $this->source instanceof ContentAddon) {
            return $this->source->content->getUrl();
        } elseif ($this->source instanceof ContentContainer) {
            return $this->source->getUrl();
        }

        return "#";
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

}
