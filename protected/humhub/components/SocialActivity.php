<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;
use yii\helpers\Html;
use humhub\modules\user\models\User;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\widgets\RichText;

/**
 * This class represents a social Activity triggered within the network.
 *
 * A SocialActivity can be assigned with an originator User, which triggered the activity and a source ActiveRecord.
 * The source is used to connect the SocialActivity to a related Content, ContentContainerActiveRecord or any other
 * ActiveRecord.
 *
 * Since SocialActivities need to be rendered in most cases it implements the humhub\components\rendering\Viewable interface and provides
 * a default implementation of the getViewParams function.
 *
 * @since 1.1
 * @author buddha
 */
abstract class SocialActivity extends \yii\base\Object implements rendering\Viewable, \Serializable
{

    /**
     * User which performed the activity.
     *
     * @var \humhub\modules\user\models\User
     */
    public $originator;

    /**
     * The source instance which created this activity
     *
     * @var \yii\db\ActiveRecord
     */
    public $source;

    /**
     * @var string the module id which this activity belongs to (required)
     */
    public $moduleId;

    /**
     * An SocialActivity can be represented in the database as ActiveRecord.
     * By defining the $recordClass an ActiveRecord will be created automatically within the
     * init function.
     *
     * @var \yii\db\ActiveRecord The related record for this activitiy
     */
    public $record;

    /**
     * @var string Record class used for instantiation.
     */
    public $recordClass;

    /**
     * @var string view name used for rendering the activity
     */
    public $viewName = 'default.php';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->recordClass) {
            $this->record = Yii::createObject($this->recordClass);
            $this->record->class = $this->className();
            $this->record->module = $this->moduleId;
        }
    }

    /**
     * Static initializer should be prefered over new initialization, since it makes use
     * of Yii::createObject dependency injection/configuration.
     *
     * @return \humhub\components\SocialActivity
     */
    public static function instance($options = [])
    {
        return Yii::createObject(static::class, $options);
    }

    /**
     * Builder function for the originator.
     *
     * @param type $originator
     * @return \humhub\components\SocialActivity
     */
    public function from($originator)
    {
        $this->originator = $originator;

        return $this;
    }

    /**
     * Builder function for the source.
     * @param type $source
     * @return \humhub\components\SocialActivity
     */
    public function about($source)
    {
        $this->source = $source;
        $this->record->setPolymorphicRelation($source);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getViewName()
    {
        // If no suffix is given, we assume a php file.
        if (!strpos($this->viewName, '.')) {
            return $this->viewName . '.php';
        } else {
            return $this->viewName;
        }
    }

    /**
     * @inheritdoc
     */
    public function getViewParams($params = [])
    {
        $result = [
            'originator' => $this->originator,
            'source' => $this->source,
            'contentContainer' => $this->getContentContainer(),
            'space' => $this->getSpace(),
            'record' => $this->record,
            'url' => $this->getUrl(),
            'viewable' => $this,
            'html' => $this->html(),
            'text' => $this->text()
        ];

        return \yii\helpers\ArrayHelper::merge($result, $params);
    }

    /**
     * Returns the related content instance in case the source is of type ContentOwner.
     *
     * @return \humhub\modules\content\models\Content Content ActiveRecord or null if not related to a ContentOwner source
     */
    public function getContent()
    {
        if ($this->hasContent()) {
            return $this->source->content;
        }

        return null;
    }

    /**
     * @return Space related space instance in case the activity source is an related contentcontainer of type space, otherwise null
     */
    public function getSpace()
    {
        $container = $this->getContentContainer();

        return ($container instanceof Space) ? $container : null;
    }

    /**
     * @return integer related space id in case the activity source is an related contentcontainer of type space, otherwise null
     */
    public function getSpaceId()
    {
        $space = $this->getSpace();

        return ($space) ? $space->id : null;
    }

    /**
     * Determines if this activity is related to a content. This is the case if the activitiy source
     * is of type ContentOwner.
     *
     * @return boolean true if this activity is related to a ContentOwner else false
     */
    public function hasContent()
    {
        return $this->source instanceof ContentOwner;
    }

    /**
     * Determines if the activity source is related to an ContentContainer.
     * This is the case if the source is either a ContentContainerActiveRecord itself or a ContentOwner.
     *
     * @return ContentContainerActiveRecord
     */
    public function getContentContainer()
    {
        if ($this->source instanceof ContentContainerActiveRecord) {
            return $this->source;
        } else if ($this->hasContent()) {
            return $this->getContent()->getContainer();
        }

        return null;
    }

    /**
     * Url of the origin of this notification
     * If source is a Content / ContentAddon / ContentContainer this will automatically generated.
     *
     * @return string
     */
    public function getUrl()
    {
        $url = '#';

        if ($this->hasContent()) {
            $url = $this->getContent()->getUrl();
        } elseif ($this->source instanceof ContentContainerActiveRecord) {
            $url = $this->source->getUrl();
        }

        // Create absolute URL, for E-Mails
        if (substr($url, 0, 4) !== 'http') {
            $url = \yii\helpers\Url::to($url, true);
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    public function text()
    {
        $html = $this->html();

        return !empty($html) ? strip_tags($html) : null;
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function json()
    {
        return \yii\helpers\Json::encode($this->asArray());
    }

    /**
     * Returns an array representation of this notification.
     */
    public function asArray(User $user)
    {
        $result = [
            'class' => $this->className(),
            'text' => $this->text(),
            'html' => $this->html()
        ];

        if ($this->originator) {
            $result['originator_id'] = $this->originator->id;
        }

        if ($this->source) {
            $result['source_class'] = $this->source->className();
            $result['source_pk'] = $this->source->getPrimaryKey();
            $result['space_id'] = $this->source->getSpaceId();
        }

        return $result;
    }

    /**
     * Build info text about a content
     *
     * This is a combination a the type of the content with a short preview
     * of it.
     *
     * If no $content is provided the contentInfo of $source is returned.
     *
     * @param Content $content
     * @return string
     */
    public function getContentInfo(ContentOwner $content = null)
    {
        if (!$this->hasContent() && !$content) {
            return;
        } else if (!$content) {
            $content = $this->source;
        }

        $truncatedDescription = RichText::widget([
            'text' => $content->getContentDescription(),
            'minimal' => true,
            'maxLength' => 60
        ]);

        $trimmed = \humhub\libs\Helpers::trimText($truncatedDescription, 60);

        return Html::encode($content->getContentName()) . ' "' . $trimmed . '"';

    }

    /**
     * Returns the content name of $content or if not $content is provided of the
     * notification source.
     *
     * @param ContentOwner $content
     * @return type
     */
    public function getContentName(ContentOwner $content = null)
    {
        if (!$this->hasContent() && !$content) {
            return;
        } else if (!$content) {
            $content = $this->source;
        }

        return $content->getContentName();
    }

    /**
     * Returns a short preview text of the content. The max length can be defined by setting
     * $maxLength (25 by default).
     *
     *  If no $content is provided the contentPreview of $source is returned.
     *
     * @param Content $content
     * @return string
     */
    public function getContentPreview(ContentOwner $content = null, $maxLength = 25)
    {
        if (!$this->hasContent() && !$content) {
            return;
        } else if (!$content) {
            $content = $this->source;
        }

        return RichText::widget([
            'text' => $content->getContentDescription(),
            'minimal' => true,
            'maxLength' => $maxLength
        ]);
    }

    /**
     * Serializes the $source and $originator fields.
     *
     * @see ActiveRecord::serialize() for the serialization of your $source
     * @link http://php.net/manual/en/function.serialize.php
     * @since 1.2
     * @return string
     */
    public function serialize()
    {
        return serialize([
            'source' => $this->source,
            'originator' => $this->originator
        ]);
    }

    /**
     * Unserializes the given string, calls the init() function and sets the $source and $originator fields (and $record indirectyl).
     *
     * @see ActiveRecord::unserialize() for the serialization of your $source
     * @link http://php.net/manual/en/function.unserialize.php
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->init();
        $unserializedArr = unserialize($serialized);
        $this->from($unserializedArr['originator']);
        $this->about($unserializedArr['source']);
    }

}
