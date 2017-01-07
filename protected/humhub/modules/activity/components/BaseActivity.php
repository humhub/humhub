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
use humhub\modules\content\models\Content;

/**
 * BaseActivity is the base class for all activities.
 *
 * @author luke
 */
abstract class BaseActivity extends \humhub\components\SocialActivity
{

    /**
     * Default content visibility of this Activity.
     * @var int
     */
    public $visibility = Content::VISIBILITY_PRIVATE;

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
     * @inheritdoc
     */
    public $recordClass = Activity::class;

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

        parent::init();
    }
    
    public function render($params = array())
    {
        
    }

    /**
     * @inheritdoc
     */
    public function getViewParams($params = [])
    {
        $params['clickable'] = $this->clickable;
        return parent::getViewParams($params);
    }
    
    public function save()
    {
        return $this->record->save();
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

        $this->saveModelInstance();
    }
    
    /**
     * @inheritdoc
     */
    public function from($originator)
    {
        parent::from($originator);
        $this->record->content->created_by = $originator->id;
        return $this;
    }
    
    /**
     * @inheritdoc
     */
    public function about($source)
    {
        parent::about($source);
        $this->record->content->visibility = $this->getContentVisibility();
        return $this;
    }


    /**
     * Builder function for setting ContentContainerActiveRecord
     * @param \humhub\modules\content\components\ContentContainerActiveRecord $container
     */
    public function container($container)
    {
        $this->record->content->container = $container;
        return $this;
    }

    private function saveModelInstance()
    {
        $this->record->setPolymorphicRelation($this->source);
        $this->record->content->visibility = $this->getContentVisibility();

        if (!$this->record->content->container) {
            $this->record->content->container = $this->getContentContainer();
            
        }

        $this->record->content->created_by = $this->getOriginatorId();

        if ($this->record->content->created_by == null) {
            throw new \yii\base\InvalidConfigException("Could not determine originator for activity!");
        }

        if (!$this->record->save()) {
            throw new \yii\base\Exception("Could not save activity!" . $this->record->getErrors());
        }
    }

    protected function getContentVisibility()
    {
        return $this->hasContent() ? $this->getContent()->visibility : $this->visibility;
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
