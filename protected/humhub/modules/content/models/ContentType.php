<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\models;


use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use Yii;
use yii\base\Model;
use yii\db\Query;

/**
 * The ContentType class serves as meta data class for content types and can be used to
 * search for existing types of [[\humhub\modules\content\components\ContentActiveRecords]].
 *
 * ContentType models are usually used for Picker widgets e.g. ContentType filters in a stream. The [[getContentTypes()]]
 * and [[getContentTypeSelection()]] function will search for content types with `stream_channel="default"` and an existing
 * content entry related to a given container or
 *
 * ## Usage:
 *
 * ```php
 * // Fetch content types
 * $spaceContentTypes = ContentType::getContentTypes($space);
 * ```
 *
 * @since 1.3
 */
class ContentType extends Model
{
    /**
     * @var string $typeClass
     */
    public $typeClass;

    /**
     * @var ContentActiveRecord instance
     */
    public $instance;

    /**
     * @var [] caches the result for contentContainer and global requests [$contentContainer->id|'' => ContentType[]]
     */
    private static $cache = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->instance = Yii::createObject($this->typeClass);
    }

    public static function flush()
    {
        static::$cache = [];
    }

    /**
     * @param ContentContainerActiveRecord|null $container
     * @return static[] existing content types of the given container
     */
    public static function getContentTypes(ContentContainerActiveRecord $container = null) {
        $containerId = ($container) ? $container->id : '';

        if(isset(static::$cache[$containerId])) {
            return static::$cache[$containerId];
        }

        $query = (new Query())->select('object_model')
            ->from('content')->distinct()
            ->where(['stream_channel' => 'default']);

        if($container) {
            $query->andWhere(['contentcontainer_id' => $container->contentcontainer_id]);
        }

        $result = [];

        foreach($query->orderBy('object_model')->all() as $item) {
            $result[] = new static(['typeClass' => $item['object_model']]);
        }

        return static::$cache[$containerId] = $result;
    }

    /**
     * @param ContentContainerActiveRecord|null $container
     * @return array content type selection array in form of [contentTypeClass => contentName]
     */
    public static function getContentTypeSelection(ContentContainerActiveRecord $container = null) {
        $result = [];
        foreach (static::getContentTypes($container) as $contentType) {
            $result[$contentType->typeClass] = $contentType->getContentName();
        }
        return $result;
    }

    /**
     * Returns the name of this type of content.
     * You need to override this method in your content implementation.
     *
     * @return string the name of the content
     */
    public function getContentName()
    {
        return $this->instance->getContentName();
    }

    /**
     * Returns a content type related icon
     * @return string
     */
    public function getIcon()
    {
        return $this->instance->getIcon();
    }

}
