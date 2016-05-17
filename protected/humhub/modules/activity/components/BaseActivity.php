<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\components;

use humhub\modules\activity\models\Activity;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * BaseActivity is the base class for all activities.
 *
 * @author luke
 */
abstract class BaseActivity extends \humhub\components\SocialActivity
{

    /**
     * @var int
     */
    public $visibility = 1;

    /**
     * @inheritdoc
     */
    public $layoutWeb = "@humhub/modules/activity/views/layouts/web.php";

    /**
     * @var string the layotu file for mail view
     */
    public $layoutMail = "@humhub/modules/activity/views/layouts/mail.php";

    /**
     * @inheritdoc
     */
    public $layoutMailPlaintext = "@humhub/modules/notification/views/layouts/mail_plaintext.php";

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
     * @inheritdoc
     */
    public function getViewParams($params = [])
    {
        $params['clickable'] = $this->clickable;

        return parent::getViewParams($params);
    }

    /**
     * Creates an activity model and determines the contentContainer/visibility
     *
     * @throws \yii\base\Exception
     */
    public function create()
    {
        if ($this->moduleId == "") {
            throw new \yii\base\InvalidConfigException("No moduleId given!");
        }

        if (!$this->source instanceof \yii\db\ActiveRecord) {
            throw new \yii\base\InvalidConfigException("Invalid source object given!");
        }

        if ($this->container == null) {
            $this->container = $this->getContentContainerFromSource();

            if ($this->container == null) {
                throw new \yii\base\InvalidConfigException("Could not determine content container for activity!");
            }
        }

        $this->saveModelInstance();
    }

    protected function getContentContainerFromSource()
    {
        if ($this->hasContentSource()) {
            return $this->source->content->container;
        } elseif ($this->source instanceof ContentContainerActiveRecord) {
            return $this->source;
        }
    }

    protected function hasContentSource()
    {
        return $this->source instanceof ContentActiveRecord || $this->source instanceof ContentAddonActiveRecord;
    }

    private function saveModelInstance()
    {
        $model = new Activity();
        $model->class = $this->className();
        $model->module = $this->moduleId;
        $model->object_model = $this->source->className();
        $model->object_id = $this->source->getPrimaryKey();
        $model->content->container = $this->container;
        $model->content->visibility = $this->getContentVisibility();
        $model->content->created_by = $this->getOriginatorId();

        if ($model->content->created_by == null) {
            throw new \yii\base\InvalidConfigException("Could not determine originator for activity!");
        }

        if (!$model->validate() || !$model->save()) {
            throw new \yii\base\Exception("Could not save activity!" . $model->getErrors());
        }
    }

    protected function getContentVisibility()
    {
        return $this->hasContentSource() ? $this->source->content->visibility : $this->visibility;
    }

    protected function getOriginatorId()
    {
        if ($this->originator !== null) {
            return $this->originator->id;
        } elseif ($this->source instanceof ContentActiveRecord) {
            return $this->source->content->created_by;
        } elseif ($this->source instanceof ContentAddonActiveRecord) {
            return $this->source->created_by;
        }
    }

}
