<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\forms;

use Yii;

/**
 * Cron Form
 *
 * @since 0.5
 */
class CronForm extends \yii\base\Model
{

    /**
     * @var boolean create sample data
     */
    public $cron;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['cron'],
                'compare',
                'compareValue' => true,
                'operator' => '==',
                'type' => 'boolean',
                'message' => Yii::t('InstallerModule.base', 'Confirm that cron jobs are configured.')
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cron' => Yii::t('InstallerModule.base', 'Cron has been configured.'),
        ];
    }

}
