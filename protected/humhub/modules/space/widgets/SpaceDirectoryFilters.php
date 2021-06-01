<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\libs\Html;
use humhub\widgets\DirectoryFilters;
use Yii;

/**
 * SpaceDirectoryFilters displays the filters on the directory spaces page
 *
 * @since 1.9
 * @author Luke
 */
class SpaceDirectoryFilters extends DirectoryFilters
{
    /**
     * @inheritdoc
     */
    public $pageUrl = '/space/spaces';

    protected function initDefaultFilters()
    {
        $this->addFilter('keyword', [
                'title' => Yii::t('SpaceModule.base', 'Free text search in the directory (name, description, tags, etc.)'),
                'placeholder' => Yii::t('SpaceModule.base', 'search for spaces'),
                'type' => 'input',
                'wrapperClass' => 'col-md-6 form-search-filter-keyword',
                'afterInput' => Html::submitButton('<span class="fa fa-search"></span>', ['class' => 'form-button-search']),
                'sortOrder' => 100,
            ]);

        $this->addFilter('sort', [
                'title' => Yii::t('SpaceModule.base', 'Sorting'),
                'type' => 'dropdown',
                'options' => [
                    'name' => Yii::t('SpaceModule.base', 'Name'),
                    'newer' => Yii::t('SpaceModule.base', 'Newer spaces'),
                    'older' => Yii::t('SpaceModule.base', 'Older spaces'),
                ],
                'sortOrder' => 200,
            ]);

        $this->addFilter('connection', [
                'title' => Yii::t('SpaceModule.base', 'Connection'),
                'type' => 'dropdown',
                'options' => [
                    '' => Yii::t('SpaceModule.base', 'All'),
                    'member' => Yii::t('SpaceModule.base', 'Member'),
                    'follow' => Yii::t('SpaceModule.base', 'Follow'),
                ],
                'sortOrder' => 300,
            ]);
    }

    public static function getDefaultValue(string $filter): string
    {
        switch ($filter) {
            case 'sort':
                return 'name';
        }

        return parent::getDefaultValue($filter);
    }
}
