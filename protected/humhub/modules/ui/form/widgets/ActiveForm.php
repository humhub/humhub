<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;


/**
 * ActiveForm
 *
 * @since 1.1
 * @author Luke
 */
class ActiveForm extends \yii\bootstrap\ActiveForm
{

    /**
     * @inheritdoc
     */
    public $enableClientValidation = false;

    /**
     * @inheritdoc
     */
    public $fieldClass = 'humhub\modules\ui\form\widgets\ActiveField';

}
