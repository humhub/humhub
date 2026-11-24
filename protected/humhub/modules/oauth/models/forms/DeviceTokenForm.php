<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models\forms;

use Yii;
use yii\base\Model;

/**
 * The name of the token a device connection creates (`ConnectController`).
 *
 * @since 1.20
 */
class DeviceTokenForm extends Model
{
    public $name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'trim'],
            ['name', 'required'],
            ['name', 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('OauthModule.base', 'Name'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'name' => Yii::t('OauthModule.base', 'The password is listed with this name under "Devices & Access", e.g. with the name of your device.'),
        ];
    }
}
