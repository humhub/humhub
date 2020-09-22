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
    public $fieldClass = ActiveField::class;

    public $acknowledge = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->acknowledge) {
            $this->options['data-ui-addition'] = 'acknowledgeForm';
        }
    }

}
