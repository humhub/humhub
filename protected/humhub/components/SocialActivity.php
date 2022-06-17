<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\components\behaviors\PolymorphicRelation;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use humhub\modules\user\models\User;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\content\interfaces\ContentOwner;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use Exception;

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
abstract class SocialActivity extends BaseObject implements rendering\Viewable
{

    /**
     * User which performed the activity.
     *
     * @var \humhub\modules\user\models\User
     */
    public $originator;

    /**
     * @var bool ensure originator existence
     * @since 1.3
     */
    public $requireOriginator = true;

    /**
     * The source instance which created this activity
     *
     * @var \yii\db\ActiveRecord
     */
    public $source;

    /**
     * @var bool ensure source existence
     * @since 1.3
     */
    public $requireSource = true;

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
     * @param array $options
     * @return static
     * @throws \yii\base\InvalidConfigException
     */
    public static function instance($options = [])
    {
        return Yii::createObject(static::class, $options);
    }

    /**
     * Builder function for the originator.
     *
     * @param User $originator
     * @return \humhub\components\SocialActivity
     */
    public function from($originator)
    {
        $this->originator = $originator;

        return $this;
    }

    /**
     * Builder function for the source.
     * @param \yii\db\ActiveRecord $source
     * @return $this
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
     * @throws \yii\base\Exception
     */
    public function getSpace()
    {
        $container = $this->getContentContainer();

        return ($container instanceof Space) ? $container : null;
    }

    /**
     * @return integer related space id in case the activity source is an related contentcontainer of type space, otherwise null
     * @throws \yii\base\Exception
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
     * @throws \yii\base\Exception
     */
    public function getContentContainer()
    {
        if ($this->source instanceof ContentContainerActiveRecord) {
            return $this->source;
        } elseif ($this->hasContent()) {
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

        if ($this->source instanceof Comment) {
            $url = $this->source->getUrl();
        } else if ($this->hasContent()) {
            $url = $this->getContent()->getUrl();
        } elseif ($this->source instanceof ContentContainerActiveRecord) {
            $url = $this->source->getUrl();
        }

        // Create absolute URL, for E-Mails
        if (substr($url, 0, 4) !== 'http') {
            $url = Url::to($url, true);
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    public function text()
    {
        $html = $this->html();

        return !empty($html) ? html_entity_decode(strip_tags($html)) : null;
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
        return Json::encode($this->asArray());
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
     * This is a combination of the type of the content with a short preview
     * of it.
     *
     * If no $content is provided the contentInfo of $source is returned.
     *
     * @param Content $content
     * @return string|null
     * @throws Exception
     */
    public function getContentInfo(ContentOwner $content = null, $withContentName = true)
    {
        if (!$this->hasContent() && !$content) {
            return null;
        }

        if (!$content) {
            $content = $this->source;
        }

        $info = $this->getContentPreview($content, 60);

        if (empty($info)) {
            return null;
        }

        return ($withContentName) ? Html::encode($content->getContentName()) . ' "' . $info . '"' : $info;
    }

    /**
     * Returns a short preview text of the content. The max length can be defined by setting
     * $maxLength (60 by default).
     *
     *  If no $content is provided the contentPreview of $source is returned.
     *
     * @param ContentOwner $content
     * @param int $maxLength
     * @return string|null
     * @throws Exception
     */
    public function getContentPreview(ContentOwner $content = null, $maxLength = 60)
    {
        if (!$this->hasContent() && !$content) {
            return null;
        }

        if (!$content) {
            $content = $this->source;
        }

        return RichTextToShortTextConverter::process($content->getContentDescription(), [
            RichTextToShortTextConverter::OPTION_MAX_LENGTH => $maxLength,
            RichTextToShortTextConverter::OPTION_CACHE_KEY => RichTextToShortTextConverter::buildCacheKeyForContent($content),
        ]);
    }

    /**
     * Builds plain text info text about a given content
     *
     * This is a combination of the type of the content with a short preview
     * of it.
     *
     * Note: This should only be used for mail subjects and other plain text
     *
     * If no $content is provided the contentInfo of $source is returned.
     *
     * @param Content $content
     * @return string|null
     * @throws Exception
     * @since 1.4
     */
    public function getContentPlainTextInfo(ContentOwner $content = null, $withContentName = true)
    {
        if (!$this->hasContent() && !$content) {
            return null;
        }

        if (!$content) {
            $content = $this->source;
        }

        $info = $this->getContentPlainTextPreview($content);

        return ($withContentName) ? $content->getContentName() . ' "' . $info . '"' : $info;
    }

    /**
     * Returns a short preview text of the content in plain text. The max length can be defined by setting
     * $maxLength (60 by default).
     *
     *  If no $content is provided the contentPreview of $source is returned.
     *
     * Note: This should only be used for mail subjects and other plain text
     *
     * @param ContentOwner $content
     * @param int $maxLength
     * @return string|null
     * @throws Exception
     * @since 1.4
     */
    public function getContentPlainTextPreview(ContentOwner $content = null, $maxLength = 60)
    {
        if (!$this->hasContent() && !$content) {
            return null;
        }

        if (!$content) {
            $content = $this->source;
        }

        try {
            return RichTextToPlainTextConverter::process($content->getContentDescription(), [
                RichTextToPlainTextConverter::OPTION_MAX_LENGTH => $maxLength,
                RichTextToPlainTextConverter::OPTION_CACHE_KEY => RichTextToPlainTextConverter::buildCacheKeyForContent($content),
            ]);
        } catch (\Exception $e) {
            Yii::error($e);
        }

        return '';
    }


    /**
     * Returns the content name of $content or if not $content is provided of the
     * notification source.
     *
     * @param ContentOwner $content
     * @return string|null
     */
    public function getContentName(ContentOwner $content = null)
    {
        if (!$this->hasContent() && !$content) {
            return null;
        }

        if (!$content) {
            $content = $this->source;
        }

        return $content->getContentName();
    }

    /**
     * Validates the existence of required attributes
     *
     * @return bool
     */
    public function validate()
    {
        if (empty($this->source) && $this->requireSource) {
            return false;
        }

        if (empty($this->originator) && $this->requireOriginator) {
            return false;
        }

        return true;
    }

    /**
     * Serializes the $source and $originator fields.
     *
     * @return array
     * @link http://php.net/manual/en/function.serialize.php
     * @since 1.2
     * @see ActiveRecord::serialize() for the serialization of your $source
     */
    public function __serialize(): array
    {
        $sourceClass = null;
        $sourcePk = null;

        if ($this->source) {
            $sourceClass = get_class($this->source);
            $sourcePk = $this->source->getPrimaryKey();
        }

        $originatorId = ($this->originator != null) ? $this->originator->id : null;

        return [
            'sourceClass' => $sourceClass,
            'sourcePk' => $sourcePk,
            'originator_id' => $originatorId
        ];
    }

    /**
     * Unserializes the given string, calls the init() function and sets the $source and $originator fields (and $record indirectyl).
     *
     * @param array $serialized
     * @link http://php.net/manual/en/function.unserialize.php
     * @see ActiveRecord::unserialize() for the serialization of your $source
     */
    public function __unserialize($unserializedArr)
    {
        $this->init();

        if (isset($unserializedArr['originator_id'])) {
            $user = User::findOne(['id' => $unserializedArr['originator_id']]);
            if ($user !== null) {
                $this->from($user);
            }
        }

        // Temporary for 1.3.0-beta.2 to 1.3.0-beta.3 updates with existing queue record
        if (isset($unserializedArr['source'])) {
            $this->about($unserializedArr['source']);
        }

        if (!empty($unserializedArr['sourceClass']) && !empty($unserializedArr['sourcePk'])) {
            $source = PolymorphicRelation::loadActiveRecord($unserializedArr['sourceClass'], $unserializedArr['sourcePk']);
            if ($source !== null) {
                $this->about($source);
            }
        }

    }
}
