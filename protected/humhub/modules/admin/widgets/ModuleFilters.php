<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\libs\Html;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\widgets\DirectoryFilters;
use Yii;

/**
 * ModuleFilters displays the filters on the modules list
 *
 * @since 1.11
 * @author Luke
 */
class ModuleFilters extends DirectoryFilters
{
    /**
     * @inheritdoc
     */
    public $pageUrl = '/admin/module/list';

    /**
     * @inheritdoc
     */
    public $paginationUsed = false;

    protected function initDefaultFilters()
    {
        $this->addFilter('keyword', [
            'title' => Yii::t('AdminModule.base', 'Search'),
            'placeholder' => Yii::t('AdminModule.base', 'Search...'),
            'type' => 'input',
            'wrapperClass' => 'col-md-7 form-search-filter-keyword',
            'afterInput' => Html::submitButton(Icon::get('search'), ['class' => 'form-button-search']),
            'sortOrder' => 100,
        ]);
    }

}
