<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\helpers\Html;
use humhub\modules\user\assets\PermissionGridModuleFilterAsset;
use humhub\widgets\JsWidget;
use Yii;

/**
 * Renders a dropdown in order to filter the permission overview by module.
 */
class PermisionGridModuleFilter extends JsWidget
{
    /**
     * @inheritdocs
     */
    public $jsWidget = 'user.PermissionGridModuleFilter';

    /**
     * @inheritdocs
     */
    public $init = true;

    /**
     * @inheritdocs
     */
    public function run()
    {
        PermissionGridModuleFilterAsset::register($this->view);
        return Html::dropDownList('', [], ['all' => Yii::t('base', 'All')], $this->getOptions());
    }

    public function getData()
    {
        return [
            'action-change' => 'change',
        ];
    }

    public function getAttributes()
    {
        return [
            'class' => 'form-control float-end d-none d-lg-block d-xl-none',
            'style' => 'width:150px',
        ];
    }

}
