<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\models;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\post\Module;
use humhub\modules\post\permissions\CreatePost;
use humhub\modules\post\widgets\WallEntry;
use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property string|null $title
 * @property string $message
 * @property string $url
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class Post extends ContentActiveRecord
{
    /**
     * @inheritdoc
     */
    public $wallEntryClass = WallEntry::class;

    /**
     * @inheritdoc
     */
    public $moduleId = 'post';

    /**
     * @inheritdoc
     */
    public $canMove = CreatePost::class;

    /**
     * Scenario - when validating with ajax
     */
    public const SCENARIO_AJAX_VALIDATION = 'ajaxValidation';

    /**
     * Scenario - when related content has attached files
     */
    public const SCENARIO_HAS_FILES = 'hasFiles';

    /**
     * @inheritdoc
     */
    protected $createPermission = CreatePost::class;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['message'], 'required', 'except' => [self::SCENARIO_AJAX_VALIDATION, self::SCENARIO_HAS_FILES]],
            [['message'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['url'], 'string', 'max' => 255],
        ];

        if (Yii::$app->getModule('post')->getTitleMode() === Module::TITLE_MODE_REQUIRED) {
            $rules[] = [['title'], 'required'];
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // Check if Post Contains an Url
        if (preg_match('/http(.*?)(\s|$)/i', $this->message)) {
            // Set Filter Flag
            $this->url = 1;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        RichText::postProcess($this->message, $this, 'message');
    }

    /**
     * @inheritdoc
     */
    public function getContentName()
    {
        return Yii::t('PostModule.base', 'post');
    }

    /**
     * @inheritdoc
     */
    public function getBadges($result = [], $includeContentName = true)
    {
        return parent::getBadges($result, false);
    }

    /**
     * @inheritdoc
     */
    public function getIcon()
    {
        return 'fa-comment-o';
    }

    /**
     * @inheritdoc
     */
    public function getContentDescription()
    {
        return !empty($this->title) ? $this->title : $this->message;
    }

    /**
     * @inheritdoc
     */
    public function getSearchAttributes()
    {
        return [
            'message' => $this->message,
        ];
    }


    /**
     * @inheritDoc
     */
    public function getUrl()
    {
        return Url::to(['/post/post/view', 'id' => $this->id, 'contentContainer' => $this->content->container]);
    }

}
