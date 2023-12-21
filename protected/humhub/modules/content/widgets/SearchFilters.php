<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\libs\Html;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\search\engine\Search;
use humhub\modules\ui\widgets\DirectoryFilters;
use Yii;

/**
 * SpaceDirectoryFilters displays the filters on the directory spaces page
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
            'placeholder' => Yii::t('SpaceModule.base', 'Search...'),
            'type' => 'input',
            'wrapperClass' => 'col-md-6 form-search-filter-keyword',
            'afterInput' => Html::submitButton('<span class="fa fa-search"></span>', ['class' => 'form-button-search']),
            'sortOrder' => 100,
        ]);

        $this->addFilter('sort', [
            'title' => Yii::t('SpaceModule.base', 'Sorting'),
            'type' => 'dropdown',
            'options' => [
                SearchRequest::ORDER_BY_SCORE => Yii::t('SpaceModule.base', 'Best'),
                SearchRequest::ORDER_BY_CREATION_DATE => Yii::t('SpaceModule.base', 'Newest first'),
            ],
            'sortOrder' => 200,
        ]);

        $this->addFilter('contentType', [
            'title' => Yii::t('SpaceModule.base', 'Content type'),
            'type' => 'dropdown',
            'options' => array_merge(['' => Yii::t('SpaceModule.base', 'Any')], SearchRequest::getContentTypes()),
            'sortOrder' => 300,
        ]);

        /*
        $this->addFilter('dateForm', [
            'title' => Yii::t('SpaceModule.base', 'Date From'),
            'type' => 'input',
            'sortOrder' => 400,
        ]);

        $this->addFilter('dateTo', [
            'title' => Yii::t('SpaceModule.base', 'Date To'),
            'type' => 'input',
            'sortOrder' => 420,
        ]);
        */

        $this->addFilter('topic', [
            'title' => Yii::t('SpaceModule.base', 'Topic'),
            'type' => 'input',
            'sortOrder' => 420,
        ]);

        $this->addFilter('author', [
            'title' => Yii::t('SpaceModule.base', 'Author'),
            'type' => 'input',
            'sortOrder' => 500,
        ]);

        /*
        $this->addFilter('status', [
            'title' => Yii::t('SpaceModule.base', 'Status'),
            'type' => 'dropdown',
            'options' => [
                '' => 'Any',
                'archived' => 'Archived',
            ],
            'sortOrder' => 500,
        ]);
        */
        $this->addFilter('space', [
            'title' => Yii::t('SpaceModule.base', 'Space'),
            'type' => 'input',
            'sortOrder' => 500,
        ]);

        /*
        $this->addFilter('profile', [
            'title' => Yii::t('SpaceModule.base', 'Profile'),
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
}
