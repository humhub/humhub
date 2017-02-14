<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

/**
 * BaseFileHandler
 * 
 * @since 1.2
 * @author Luke
 */
abstract class BaseFileHandler extends \yii\base\Component
{

    /**
     * @var \humhub\modules\file\models\File the file
     */
    public $file;

    /**
     * The file handler link
     * 
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     * @see \humhub\modules\file\widgets\FileHandlerButtonDropdown
     * @return array the HTML attributes of the button.
     */
    abstract public function getLinkAttributes();
}
