<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\libs\Html;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\SpacePickerField;
use humhub\modules\topic\models\Topic;
use humhub\modules\topic\widgets\TopicPicker;
use humhub\modules\ui\widgets\DirectoryFilters;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\UserPickerField;
use Yii;

/**
 * SearchFilters displays the filters on the content searching page
 *
 * @since 1.9
 * @author Luke
 */
class SearchFilters extends DirectoryFilters
{
    /**
     * @inheritdoc
     */
    public $pageUrl = '/content/search';

    protected function initDefaultFilters()
    {
        $this->addFilter('keyword', [
            'title' => Yii::t('ContentModule.search', 'Find Content based on keywords'),
            'placeholder' => Yii::t('ContentModule.search', 'Search...'),
            'type' => 'input',
            'inputOptions' => ['autocomplete' => 'off', 'data-highlight' => '.search-results'],
            'wrapperClass' => 'col-md-6 form-search-filter-keyword',
            'afterInput' => Html::submitButton('<span class="fa fa-search"></span>', ['class' => 'form-button-search']),
            'sortOrder' => 100,
        ]);

        $this->addFilter('orderBy', [
            'title' => Yii::t('ContentModule.search', 'Sorting'),
            'type' => 'dropdown',
            'options' => [
                SearchRequest::ORDER_BY_SCORE => Yii::t('ContentModule.search', 'Best'),
                SearchRequest::ORDER_BY_CREATION_DATE => Yii::t('ContentModule.search', 'Newest first'),
            ],
            'sortOrder' => 200,
        ]);

        $this->addFilter('contentType', [
            'title' => Yii::t('ContentModule.search', 'Content type'),
            'type' => 'dropdown',
            'options' => array_merge(['' => Yii::t('ContentModule.search', 'Any')], SearchRequest::getContentTypes()),
            'sortOrder' => 300,
        ]);

        $this->addFilter('dateFrom', [
            'title' => Yii::t('ContentModule.search', 'Date From'),
            'type' => 'date',
            'sortOrder' => 400,
        ]);

        $this->addFilter('dateTo', [
            'title' => Yii::t('ContentModule.search', 'Date To'),
            'type' => 'date',
            'sortOrder' => 420,
        ]);

        $this->addFilter('topic', [
            'title' => Yii::t('ContentModule.search', 'Topic'),
            'type' => 'widget',
            'widget' => TopicPicker::class,
            'widgetOptions' => [
                'selection' => $this->getTopicsFromRequest(),
            ],
            'sortOrder' => 430,
        ]);

        $this->addFilter('author', [
            'title' => Yii::t('ContentModule.search', 'Author'),
            'type' => 'widget',
            'widget' => UserPickerField::class,
            'widgetOptions' => [
                'selection' => $this->getAuthorsFromRequest(),
                'defaultResults' => $this->getCurrentUserAuthor(),
            ],
            'sortOrder' => 500,
        ]);

        /*
        $this->addFilter('status', [
            'title' => Yii::t('ContentModule.search', 'Status'),
            'type' => 'dropdown',
            'options' => [
                '' => 'Any',
                'archived' => 'Archived',
            ],
            'sortOrder' => 500,
        ]);*/

        $this->addFilter('contentContainer', [
            'title' => Yii::t('ContentModule.search', 'Space'),
            'type' => 'widget',
            'widget' => SpacePickerField::class,
            'widgetOptions' => [
                'selection' => $this->getSpacesFromRequest(),
            ],
            'sortOrder' => 600,
        ]);

        /*
        $this->addFilter('profile', [
            'title' => Yii::t('ContentModule.search', 'Profile'),
            'type' => 'input',
            'sortOrder' => 500,
        ]);
        */
    }

    public static function getDefaultValue(string $filter): string
    {
        switch ($filter) {
            case 'sort':
                return 'sortOrder';
        }

        return parent::getDefaultValue($filter);
    }

    protected function getTopicsFromRequest(): array
    {
        $topics = Yii::$app->request->get('topic');
        if (!is_array($topics) || empty($topics)) {
            return [];
        }

        return Topic::find()->where(['IN', 'id', $topics])->all();
    }

    protected function getAuthorsFromRequest(): array
    {
        $authors = Yii::$app->request->get('author');
        if (!is_array($authors) || empty($authors)) {
            return [];
        }

        return User::find()->where(['IN', 'guid', $authors])->all();
    }

    protected function getCurrentUserAuthor(): array
    {
        if (Yii::$app->user->isGuest) {
            return [];
        }

        return User::find()->where(['id' => Yii::$app->user->id])->all();
    }

    protected function getSpacesFromRequest(): array
    {
        $spaces = Yii::$app->request->get('space');
        if (!is_array($spaces) || empty($spaces)) {
            return [];
        }

        return Space::find()->where(['IN', 'guid', $spaces])->all();
    }
}
