<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 23.07.2017
 * Time: 15:47
 */

namespace humhub\modules\content\models;

use Yii;
use yii\db\ActiveQuery;
use humhub\components\ActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use yii\db\Expression;

/**
 * ContentTags are a concept to categorize content on module and ContentContainer level.
 * By means of ContentTags a module is able to create own category concepts as for example event types (calendar) or
 * task related tags, in which the module itself is responsible for managing and the usage of it's tags.
 *
 * Module implementing own an own category concept should extend the ContentTag class as follows:
 *
 * ```php
 * class MyModuleCategory extends ContentTag
 * {
 *   public $moduleId = 'myModule';
 *
 *   public static getLabel()
 *  {
 *     return 'MyCategory'
 *  }
 * ```
 *
 * All MyModuleCategory will be assigned with the given `$moduleId` as `$module_id` and the class name as `$type`.
 * Calls to `MyModuleCategory::find()` will always include `$module_id` filters, the same goes for other query methods as `findByCotnainer` etc.
 *
 * If a subclass sets `$includeTypeQuery` to true queries will also include a $type filter. This is required if a module provides more than one
 * tag type.
 *
 * If you require to select untyped tags use `ContentTag::find()` instead.
 *
 * If the contentcontainer_id of a given ContentTag is null, the ContentTag is meant to be module global and therefore not bound to a ContentContainer instance.
 *
 * In some cases a ContentTag has to be related with some further settigns, this can be achieved by extending the `ContentTagAddition` class and
 * setting the ContentTags `$additioNClass`.
 *
 * An instance of this class will automatically be created by calling `$tag->addition`
 *
 * @property integer $id
 * @property string name
 * @property string module_id
 * @property integer contentcontainer_id
 * @property string type
 * @property integer parent_id
 * @property string color
 * @property integer sort_order
 *
 * @property ContentContainerActiveRecord $container
 * @property ContentContainer $contentContainer
 *
 * @package humhub\modules\content\models
 * @since 1.2.2
 * @author buddha
 */
class ContentTag extends ActiveRecord
{
    /**
     * @var string id of the module related to this content tag concept
     */
    public $moduleId;

    /**
     * @var string class of ContentTagAddition (optional), if set a call to $tag->addition will create an empty addition instance,
     * the addition will also be validated and saved in the tag save process.
     */
    public $additionClass;

    /**
     * @var bool if set to true the default search queries will include a type condition beside the module condition,
     * this is only required if one module provides multiple content tag concepts.
     */
    public $includeTypeQuery = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content_tag';
    }

    /**
     * ContentTag constructor.
     *
     * Can be called either with a yii contfig array or with contentcontainer and name.
     *
     * @param array|ContentContainerActiveRecord $contentContainer
     * @param null $name
     * @param array $config
     */
    public function __construct($contentContainer = [], $name = null, $config = [])
    {
        if (is_array($contentContainer)) {
            parent::__construct($contentContainer);
        } elseif ($contentContainer instanceof ContentContainerActiveRecord) {
            $this->contentcontainer_id = $contentContainer->contentcontainer_id;
            parent::__construct($config);
        } else {
            parent::__construct([]);
        }

        if(!empty($name)) {
            $this->name = $name;
        }
    }

    public static function getLabel()
    {
        return Yii::t('ContentModule.base', 'Tag');
    }

    /**
     * Sets the module_id as and type.
     * Subclasses overwriting this method have to make sure to call parent::init() at the end.
     */
    public function init()
    {
        $this->module_id = $this->moduleId;
        if (!$this->type) {
            $this->type = static::class;
        }
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'module_id'], 'required'],
            [['name', 'module_id', 'type'], 'string', 'max' => '100'],
            ['color', 'string', 'max' => '7'],
            [['parent_id', 'sort_order'], 'integer'],
            [['name'], 'validateUnique']
        ];
    }

    /**
     * Validates
     * @param $attribute
     * @param $params
     * @param $validator
     */
    public function validateUnique($attribute, $params, $validator)
    {
        if (empty($this->contentcontainer_id)) {
            $query = static::find()->andWhere('contentcontainer_id IS NULL');
        } else {
            $query = static::findByContainer($this->contentcontainer_id);
        }

        $query->andWhere(['name' => $this->name]);

        if (!$this->isNewRecord) {
            $query->andWhere(['<>', 'id', $this->id]);
        }

        if ($query->count() > 0) {
            $this->addError('name', Yii::t('ContentModule.base', 'The given name is already in use.'));
        }
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        /**
         * Ensure there is always a corresponding Content record
         * @see ContentTagAddition
         */
        if ($name == 'addition' && $this->additionClass) {
            $addition = parent::__get('addition');
            if (!$this->isRelationPopulated('addition') || $addition === null) {
                $addition = Yii::createObject($this->additionClass);
                $this->setAddition($addition);
            }
            return $addition;
        }
        return parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        if($attributeNames === null || in_array('addition', $attributeNames)) {
            // the addition will only be validated if $tag->addition has been called
            if($this->hasAddition() && !$this->addition->validate()) {
                return false;
            }
        }

        return parent::validate($attributeNames, $clearErrors);
    }

    public function load($data, $formName = null)
    {
        if($this->hasAddition()) {
            $this->addition->load($data);
        }
        return parent::load($data, $formName);
    }

    /**
     * Returns the ContentContainer relation as ActiveQuery to this tag or null if this is a global tag.
     *
     * Note: In order to retrieve the actual Container (Space/User) use `getContainer()`.
     * @return \yii\db\ActiveQuery
     */
    public function getContentContainer()
    {
        if ($this->contentcontainer_id === null) {
            return null;
        }

        return $this->hasOne(ContentContainer::class, ['id' => 'contentcontainer_id']);
    }

    /**
     * Returns the actual Container (Space/User) related to this tag or null if this is a global tag.
     * This function will cache the container instance once loaded.
     *
     * @return null|ContentContainerActiveRecord
     * @throws \yii\db\IntegrityException
     */
    public function getContainer()
    {
        if ($this->contentcontainer_id === null) {
            return null;
        }

        return $this->contentContainer->getPolymorphicRelation();
    }

    /**
     * @return string related link
     */
    public function getUrl()
    {
        return null;
    }


    /**
     * Returns the parent tag relation.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        if ($this->parent_id === null) {
            return null;
        }

        return $this->hasOne(ContentTag::class, ['id' => 'parent_id']);
    }

    /**
     * @return ActiveRecord|\yii\db\ActiveQuery
     */
    public function getAddition()
    {
        if (!$this->additionClass) {
            return null;
        }

        return $this->hasOne($this->additionClass, ['tag_id' => 'id']);
    }

    /**
     * @return bool check if this content tag has an related addition
     */
    public function hasAddition()
    {
        return $this->additionClass != null && $this->isRelationPopulated('addition');
    }

    /**
     * Sets a ContentTagAddition for this instance, note you'll have to call `$tag->save()` manually
     * @param ContentTagAddition $addition
     */
    public function setAddition(ContentTagAddition $addition)
    {
        $this->populateRelation('addition', $addition);
        $addition->setTag($this);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if($this->hasAddition()) {
            $this->addition->setTag($this);
            $this->addition->save();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Checks if this content tag is of the given $type.
     *
     * @param ContentTag $tag
     * @return bool
     */
    public function is($type)
    {
        return $this->type === $type;
    }

    /**
     * Finds instances and filters by module_id if a static module_id is given.
     *
     * @return \yii\db\ActiveQuery
     */
    public static function find()
    {
        $query = parent::find()
            ->orderBy('sort_order ASC')->addOrderBy('name ASC');
        return static::addQueryCondition($query);
    }

    /**
     * Adds content tag related fitler conditions to a given query.
     *
     * @param ActiveQuery $query
     * @return ActiveQuery
     * @since 1.3
     */
    public static function addQueryCondition(ActiveQuery $query)
    {
        static::moduleQuery($query);
        return static::typeQuery($query);
    }

    /**
     * Find a single instances either by providing an id or other condition.
     *
     * @return static
     */
    public static function findOne($condition)
    {
        if(is_numeric($condition)) {
            return static::find()->andWhere(['id' => $condition])->one();
        } else {
            return static::find()->andWhere($condition)->one();
        }
    }

    /**
     * Adds an type filter query to the given $query instance in case $includeTypeQuery of the subclass is set to true and the
     * calling class is not ContentTag class itself.
     *
     * @param $query ActiveQuery
     * @param bool $force
     * @return mixed
     */
    public static function typeQuery($query)
    {
        $instance = new static;
        if($instance->includeTypeQuery && static::class != ContentTag::class) {
            $query->andWhere(['content_tag.type' => static::class]);
        }

        return $query;
    }

    /**
     * Adds an module id filter query to the given $query instance in case a subclass has set the $moduleId.
     *
     * @param $query ActiveQuery
     * @return mixed
     */
    public static function moduleQuery($query)
    {
        $instance = new static;
        if (!empty($instance->moduleId)) {
            $query->andWhere(['content_tag.module_id' => $instance->moduleId]);
        }
        return $query;
    }

    /**
     * Finds ContentTag for the given $moduleId
     *
     * @param $moduleId
     * @return \yii\db\ActiveQuery
     */
    public static function findByModule($moduleId)
    {
        return parent::find()->where(['module_id' => $moduleId]);
    }

    /**
     * Finds instances by given $type.
     *
     * @param string $type
     * @return \yii\db\ActiveQuery
     */
    public static function findByType($type)
    {
        $query = parent::find();
        static::moduleQuery($query)->andWhere(['content_tag.type' => $type]);
        return $query;
    }

    /**
     * Finds instances by given name and optionally by contentContainer.
     * @param $name
     * @param ContentContainerActiveRecord|int|null $contentContainer
     * @return ActiveQuery
     */
    public static function findByName($name, $contentContainer = null)
    {
        if($contentContainer) {
            $query = static::findByContainer($contentContainer);
        } else {
            $query = static::find();
        }

        $query->andWhere(['content_tag.name' => $name]);
        return $query;
    }

    /**
     * Finds all global content tags of this type.
     * @return ActiveQuery
     */
    public static function findGlobal()
    {
        $query = static::find();
        $query->andWhere('content_tag.contentcontainer_id IS NULL');
        return $query;
    }

    /**
     * Returns Content related tags.
     *
     * @param Content $content
     * @param null $type
     * @return ActiveQuery
     */
    public static function findByContent(Content $content)
    {
        $query = $content->getTags(static::class);
        static::moduleQuery($query);
        $instance = new static;

        if (!empty($instance->moduleId)) {
            $query->andWhere(['content_tag.module_id' => $instance->moduleId]);
        }

        return static::typeQuery($query);
    }

    /**
     * Returns all tag relations of the given type for the given $content.
     *
     * @param Content $content
     * @return ContentTagRelation[]
     */
    public static function getTagContentRelations(Content $content, $includeGlobal = true)
    {
        $query = $content->getTagRelations()->innerJoin('content_tag', 'content_tag_relation.tag_id = content_tag.id');

        $instance = new static;
        if (!empty($instance->moduleId)) {
            $query->andWhere(['content_tag.module_id' => $instance->moduleId]);
        }

        if(!$includeGlobal) {
            $query->andWhere(['IS NOT', 'content_tag.contentcontainer_id', new Expression('NULL')]);
        }

        if (static::class != ContentTag::class) {
            $query->andWhere(['content_tag.type' => static::class]);
        }

        return $query->all();
    }

    /**
     * Deletes all tag relations of the given type for the given $content.
     *
     * @param Content $content
     */
    public static function deleteContentRelations(Content $content, $includeGlobal = true)
    {
        $relations = static::getTagContentRelations($content, $includeGlobal);
        foreach ($relations as $relation) {
            $relation->delete();
        }

        $content->refresh();
    }

    /**
     * Deletes all tags by module id
     * @param ContentContainerActiveRecord|int $contentContainer
     * @return int the number of rows deleted
     */
    public static function deleteByModule($contentContainer = null)
    {
        $instance = new static();

        if($contentContainer) {
            $container_id = $contentContainer instanceof ContentContainerActiveRecord ? $contentContainer->contentcontainer_id : $contentContainer;
            return static::deleteAll(['module_id' => $instance->module_id, 'contentcontainer_id' => $container_id]);
        } else {
            return static::deleteAll(['module_id' => $instance->module_id]);
        }
    }

    /**
     * Deletes all tags by type
     * @param ContentContainerActiveRecord|int $contentContainer
     * @return int the number of rows deleted
     */
    public static function deleteByType($contentContainer = null)
    {
        $instance = new static();

        if($contentContainer) {
            $container_id = $contentContainer instanceof ContentContainerActiveRecord ? $contentContainer->contentcontainer_id : $contentContainer;
            return static::deleteAll(['type' => $instance->type, 'contentcontainer_id' => $container_id]);
        } else {
            return static::deleteAll(['type' => $instance->type]);
        }
    }

    /**
     * In case this function is not called on the base ContentTag function, it will ensure to that at least either a
     * 'module_id' or 'type' condition is given to prevent deleting all content tags.
     *
     * If no 'module_id' or 'type' condition is given this function will automatically add a 'type' condition.
     *
     * @since 1.3.2
     */
    public static function deleteAll($condition = null, $params = [])
    {
        if(static::class === ContentTag::class) {
            return parent::deleteAll($condition, $params);
        }

        $instance = new static();
        if(empty($condition)) {
            $condition = ['type' => $instance->type];
        } else if(!empty($condition) && !isset($condition['module_id']) && !isset($condition['type'])) {
            $condition['type'] = $instance->type;
        }

        return parent::deleteAll($condition, $params);
    }

    /**
     * Searches for all content tags of this type.
     *
     * @param $condition
     * @since 1.3.2
     * @return ActiveRecord[]
     */
    public static function findAll($condition)
    {
        if (static::class === ContentTag::class) {
            return parent::findAll($condition);
        }

        $instance = new static();
        if(empty($condition)) {
            $condition = ['type' => $instance->type];
        } else {
            $condition['type'] = $instance->type;
        }

        return parent::findAll($condition);
    }

    /**
     * Finds instances by ContentContainerActiveRecord and optional type.
     *
     * If $includeGlobal is set to true the query will also include global content tags of this type.
     *
     * @param $container ContentContainerActiveRecord|int
     * @param bool $includeGlobal if true the query will include global tags as well @since 1.2.3
     * @return ActiveQuery
     * @internal param ContentContainerActiveRecord|int $record Container instance or contentcontainer_id
     * @internal param null $type
     */
    public static function findByContainer($container, $includeGlobal = false)
    {
        $container_id = $container instanceof ContentContainerActiveRecord ? $container->contentcontainer_id : $container;

        if(!$includeGlobal) {
            return static::find()->andWhere(['content_tag.contentcontainer_id' => $container_id]);
        } else {
            return static::find()->andWhere(['or',
                ['content_tag.contentcontainer_id' => $container_id],
                'content_tag.contentcontainer_id IS NULL',
            ]);
        }
    }
}
