<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\components;

use Yii;
use ReflectionClass;
use humhub\modules\user\models\User;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * BaseActivity is the base class for all activities.
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
     * @var string the module id which this activity belongs to (required)
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
    protected $layoutWeb = "@humhub/modules/activity/views/layouts/web.php";

    /**
     * @var string the layotu file for mail view
     */
    protected $layoutMail = "@humhub/modules/activity/views/layouts/mail.php";

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
            $this->visibility = \humhub\modules\content\models\Content::VISIBILITY_PRIVATE;
        }

        parent::init();
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
    public function getContentInfo(\humhub\modules\content\interfaces\ContentTitlePreview $content)
    {
        return \yii\helpers\Html::encode($content->getContentName()) .
                ' "' .
                \humhub\widgets\RichText::widget(['text' => $content->getContentDescription(), 'minimal' => true, 'maxLength' => 60]) . '"';
    }

    /**
     * Url of the origin of this notification
     * If source is a Content / ContentAddon / ContentContainer this will automatically generated.
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->source instanceof ContentActiveRecord || $this->source instanceof ContentAddonActiveRecord) {
            return $this->source->content->getUrl();
        } elseif ($this->source instanceof ContentContainerActiveRecord) {
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

    /**
     * Creates an activity
     *
     * @throws \yii\base\Exception
     */
    public function create()
    {
        $model = new Activity;
        $model->class = $this->className();

        if ($this->moduleId == "") {
            throw new \yii\base\InvalidConfigException("No moduleId given!");
        }

        $model->module = $this->moduleId;


        // Set content container and visibility
        if ($this->source instanceof ContentActiveRecord || $this->source instanceof ContentAddonActiveRecord) {
            $model->content->container = $this->source->content->container;
            $model->content->visibility = $this->source->content->visibility;
        } elseif ($this->source instanceof ContentContainerActiveRecord) {
            $model->content->visibility = $this->visibility;
            $model->content->container = $this->source;
        } else {
            throw new \yii\base\InvalidConfigException("Invalid source object type!");
        }

        $model->object_model = $this->source->className();
        $model->object_id = $this->source->getPrimaryKey();

        // Set user
        if ($this->originator !== null) {
            $model->content->user_id = $this->originator->id;
        } elseif ($this->source instanceof ContentActiveRecord) {
            $model->content->user_id = $this->source->content->user_id;
        } elseif ($this->source instanceof ContentAddonActiveRecord) {
            $model->content->user_id = $this->source->created_by;
        }

        if (!$model->validate() || !$model->save()) {
            throw new \yii\base\Exception("Could not save activity!" . $model->getErrors());
        }
    }

}
