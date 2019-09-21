<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\widgets;

use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\UserPickerField;
use Yii;
use humhub\modules\stream\models\filters\ContentTypeStreamFilter;
use humhub\modules\stream\models\filters\DefaultStreamFilter;
use humhub\modules\stream\models\filters\TopicStreamFilter;
use humhub\modules\content\widgets\ContentTypePicker;
use humhub\modules\topic\widgets\TopicPicker;
use humhub\modules\ui\filter\widgets\PickerFilterInput;
use humhub\modules\ui\filter\widgets\RadioFilterInput;
use humhub\modules\stream\actions\Stream;
use humhub\modules\ui\filter\widgets\FilterNavigation;

/**
 * Predefines the default stream filters of a content stream.
 *
 * The default stream filter navigation consists of three panels
 *
 * - [[PANEL_POSITION_LEFT]]
 * - [[PANEL_POSITION_CENTER]]
 * - [[PANEL_POSITION_RIGHT]]
 *
 * and the following blocks and filters:
 *
 * - basic
 *   - filter_entry_userinvolved
 *   - filter_entry_mine
 *   - filter_entry_files
 * - post
 *   - filter_model_posts
 *   - filter_posts_links
 * - visibility
 *   - filter_visibility_public
 *   - filter_visibility_private
 * - sorting
 *   - sorting_c
 *   - sorting_u
 * - content type
 *   - filter_content_type
 * - topics
 *   - filter_topic
 *
 * which are holding the following
 *
 * @since 1.3
 */
class WallStreamFilterNavigation extends FilterNavigation
{
    /**
     * Left panel position
     */
    const PANEL_POSITION_LEFT = 0;

    /**
     * Left panel position
     */
    const PANEL_POSITION_CENTER = 1;
    const PANEL_POSITION_RIGHT = 2;

    const FILTER_BLOCK_BASIC = 'basic';
    const FILTER_BLOCK_VISIBILITY = 'visibility';
    const FILTER_BLOCK_SORTING = 'sorting';
    const FILTER_BLOCK_CONTENT_TYPE = 'contentType';
    const FILTER_BLOCK_TOPIC = 'topics';
    const FILTER_BLOCK_ORIGINATORS = 'originators';

    const FILTER_USER_INVOVLED = 'entry_userinvolved';
    const FILTER_MINE = 'entry_mine';
    const FILTER_FILES = 'entry_files';

    const FILTER_VISIBILITY_PUBLIC = 'visibility_public';
    const FILTER_VISIBILITY_PRIVATE = 'visibility_private';
    const FILTER_ARCHIVED = 'entry_archived';

    const FILTER_CONTENT_TYPE = 'content_type';
    const FILTER_TOPICS = 'topic';
    const FILTER_ORIGINATORS = 'originators';

    const FILTER_SORT_CREATION = 'sort_creation';
    const FILTER_SORT_UPDATE = 'sort_update';

    public $jsWidget = 'stream.wall.WallStreamFilter';

    /**
     * @var string view
     */
    public $view = 'wallStreamFilterNavigation';

    /**
     * @inheritdoc
     */
    public $id = 'wall-stream-filter-nav';

    public $defaultBlock = self::FILTER_BLOCK_BASIC;

    /**
     * @inheritdoc
     */
    protected function initFilterPanels()
    {
        $this->filterPanels[static::PANEL_POSITION_LEFT] = [];
        $this->filterPanels[static::PANEL_POSITION_CENTER] = [];
        $this->filterPanels[static::PANEL_POSITION_RIGHT] = [];
    }

    /**
     * @inheritdoc
     */
    protected function initFilterBlocks()
    {
        $this->addFilterBlock(static::FILTER_BLOCK_BASIC, [
            'title' => Yii::t('StreamModule.filter', 'Content'),
            'sortOrder' => 100
        ], static::PANEL_POSITION_LEFT);

        $this->addFilterBlock(static::FILTER_BLOCK_VISIBILITY, [
            'title' => Yii::t('StreamModule.filter', 'Visibility'),
            'sortOrder' => 200
        ], static::PANEL_POSITION_LEFT);

        $this->addFilterBlock(static::FILTER_BLOCK_SORTING, [
            'title' => Yii::t('StreamModule.filter', 'Sorting'),
            'sortOrder' => 100
        ], static::PANEL_POSITION_CENTER);

        $this->addFilterBlock(static::FILTER_BLOCK_CONTENT_TYPE, [
            'title' => Yii::t('StreamModule.filter', 'Content Type'),
            'sortOrder' => 100
        ], static::PANEL_POSITION_RIGHT);

        $this->addFilterBlock(static::FILTER_BLOCK_ORIGINATORS, [
            'title' => Yii::t('StreamModule.filter', 'Author'),
            'sortOrder' => 200
        ], static::PANEL_POSITION_RIGHT);

        $this->addFilterBlock(static::FILTER_BLOCK_TOPIC, [
            'title' => Yii::t('StreamModule.filter', 'Topic'),
            'sortOrder' => 300
        ], static::PANEL_POSITION_RIGHT);
    }

    /**
     * @inheritdoc
     */
    protected function initFilters()
    {
       $this->initBasicFilters();
       $this->initVisibilityFilters();
       $this->initSortFilters();
       $this->initTopicFilter();
       $this->initContentTypeFilter();
       $this->initOriginatorFilter();
    }

    protected function initBasicFilters()
    {
        $this->addFilter([
            'id' => DefaultStreamFilter::FILTER_INVOLVED,
            'title' => Yii::t('ContentModule.base', 'Where I\'m involved'),
            'sortOrder' => 100
        ], static::FILTER_BLOCK_BASIC);

        $this->addFilter([
            'id' => DefaultStreamFilter::FILTER_MINE,
            'title' => Yii::t('ContentModule.base', 'Created by me'),
            'sortOrder' => 200
        ], static::FILTER_BLOCK_BASIC);

        $this->addFilter([
            'id' => DefaultStreamFilter::FILTER_FILES,
            'title' => Yii::t('ContentModule.base', 'With file attachments'),
            'sortOrder' => 300
        ], static::FILTER_BLOCK_BASIC);
    }

    protected function initVisibilityFilters()
    {
        $container = ContentContainerHelper::getCurrent();

        // Private spaces do not have public content
        if($container && $container->canAccessPrivateContent()
            && ($container instanceof User
                || ($container instanceof Space && $container->visibility != Space::VISIBILITY_NONE))) {

            $this->addFilter([
                'id' => static::FILTER_VISIBILITY_PUBLIC,
                'class' => RadioFilterInput::class,
                'radioGroup' => 'visibility',
                'multiple' => true,
                'title' => Yii::t('ContentModule.base', 'Only public content'),
                'sortOrder' => 100
            ], static::FILTER_BLOCK_VISIBILITY);

            $this->addFilter([
                'id' => static::FILTER_VISIBILITY_PRIVATE,
                'class' => RadioFilterInput::class,
                'radioGroup' => 'visibility',
                'multiple' => true,
                'title' => Yii::t('ContentModule.base', 'Only private content'),
                'sortOrder' => 200
            ], static::FILTER_BLOCK_VISIBILITY);
        }

        $this->addFilter([
            'id' => static::FILTER_ARCHIVED,
            'title' =>  Yii::t('ContentModule.base', 'Include archived content'),
            'sortOrder' => 200
        ], static::FILTER_BLOCK_VISIBILITY);
    }

    protected function initSortFilters()
    {
        $defaultSorting = Yii::$app->getModule('stream')->settings->get('defaultSort', Stream::SORT_CREATED_AT);

        $this->addFilter([
            'id' => static::FILTER_SORT_CREATION,
            'class' => RadioFilterInput::class,
            'category' => 'sort',
            'radioGroup' => 'sort',
            'force' => true,
            'title' =>  Yii::t('ContentModule.base', 'Creation time'),
            'checked' => $defaultSorting === Stream::SORT_CREATED_AT,
            'value' => Stream::SORT_CREATED_AT,
            'sortOrder' => 100
        ], static::FILTER_BLOCK_SORTING);

        $this->addFilter([
            'id' => static::FILTER_SORT_UPDATE,
            'class' => RadioFilterInput::class,
            'title' =>  Yii::t('ContentModule.base', 'Last update'),
            'category' => 'sort',
            'radioGroup' => 'sort',
            'force' => true,
            'checked' => $defaultSorting === Stream::SORT_UPDATED_AT,
            'value' => Stream::SORT_UPDATED_AT,
            'sortOrder' => 200
        ], static::FILTER_BLOCK_SORTING);
    }

    private function initTopicFilter()
    {
        $this->addFilter([
            'id' => static::FILTER_TOPICS,
            'class' => PickerFilterInput::class,
            'picker' => TopicPicker::class,
            'category' => TopicStreamFilter::CATEGORY,
            'pickerOptions' => [
                'id' => 'stream-topic-picker',
                'name' => 'stream-topic-picker',
                'addOptions' => false
            ]
        ], static::FILTER_BLOCK_TOPIC);
    }

    private function initContentTypeFilter()
    {
        $this->addFilter([
            'id' => static::FILTER_CONTENT_TYPE,
            'class' => PickerFilterInput::class,
            'picker' => ContentTypePicker::class,
            'category' => ContentTypeStreamFilter::CATEGORY_INCLUDES,
            'pickerOptions' => [
                'id' => 'stream_filter_content_type',
                'name' => 'filter_content_type'
            ]
        ], static::FILTER_BLOCK_CONTENT_TYPE);
    }

    private function initOriginatorFilter()
    {
        $this->addFilter([
            'id' => static::FILTER_ORIGINATORS,
            'class' => PickerFilterInput::class,
            'picker' => UserPickerField::class,
            'category' => 'originators',
            'pickerOptions' => [
                'id' => 'stream-user-picker',
                'itemKey' => 'id',
                'name' => 'stream-user-picker'
            ]
        ], static::FILTER_BLOCK_ORIGINATORS);
    }

    public function getAttributes()
    {
        return [
            'class' => 'wallFilterPanel'
        ];
    }
}
