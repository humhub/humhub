<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\forms;

use Yii;

/**
 * Use Case Form
 *
 * @since 0.5
 */
class UseCaseForm extends \yii\base\Model
{

    /**
     * @var string use case
     */
    public $useCase;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            array(['useCase'], 'required'),
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'useCase' => Yii::t('InstallerModule.forms_UseCaseForm', 'I want to use HumHub for:'),
        );
    }

}
