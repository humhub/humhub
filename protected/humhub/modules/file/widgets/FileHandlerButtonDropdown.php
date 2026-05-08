<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\widgets;

use humhub\components\Widget;
use humhub\helpers\Html;
use humhub\modules\file\handler\BaseFileHandler;
use yii\helpers\ArrayHelper;

/**
 * FileHandlerButtonWidget shows a dropdown with different file handlers
 *
 * @since 1.2
 * @author Luke
 * @deprecated since 1.19 Use {@see UploadButton} with the `handlers` property instead.
 */
class FileHandlerButtonDropdown extends Widget
{
    /**
     * @var string the primary button html code, if not set the first handler will be used
     */
    public $primaryButton;

    /**
     * @var string the default parent css class
     * You can make the menu drop up by replacing it with 'btn-group dropup'
     * Or smaller with `btn-group btn-group-sm`
     */
    public $cssClass = 'btn-group';

    /**
     * @var string the default css bootstrap button class
     */
    public $cssButtonClass = 'btn-success';

    /**
     * @var BaseFileHandler[] the handlers to show
     */
    public $handlers;

    /**
     * @var bool if true the dropdown-menu will be assigned with an dropdown-menu-end class.
     */
    public $pullRight = false;

    /**
     * @inheritdoc
     * @deprecated since 1.19 Use {@see UploadButton} with the `handlers` property instead.
     */
    public function run()
    {
        return '';
    }
}
