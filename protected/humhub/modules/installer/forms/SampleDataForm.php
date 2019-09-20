<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\forms;

use Yii;

/**
 * Sample Data Form
 *
 * @since 0.5
 */
class SampleDataForm extends \yii\base\Model
{

    /**
     * @var boolean create sample data
     */
    public $sampleData;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sampleData'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'sampleData' => Yii::t('InstallerModule.base', 'Set up example content (recommended)'),
        ];
    }

}
