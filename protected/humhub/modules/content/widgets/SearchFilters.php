<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\libs\Html;
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
                'name' => Yii::t('SpaceModule.base', 'Best'),
                'newer' => Yii::t('SpaceModule.base', 'Newest first'),
                'older' => Yii::t('SpaceModule.base', 'Oldest first'),
            ],
            'sortOrder' => 200,
        ]);

        $this->addFilter('Type', [
            'title' => Yii::t('SpaceModule.base', 'Content type'),
            'type' => 'dropdown',
            'options' => [
                '' => Yii::t('SpaceModule.base', 'Any'),
                'post' => Yii::t('SpaceModule.base', 'Post'),
                'wiki' => Yii::t('SpaceModule.base', 'Wiki Page'),
                'poll' => Yii::t('SpaceModule.base', 'Poll'),
            ],
            'sortOrder' => 300,
        ]);
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
