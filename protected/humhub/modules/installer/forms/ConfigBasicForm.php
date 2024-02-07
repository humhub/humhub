<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\forms;

use Yii;
use yii\base\Model;

/**
 * ConfigBasicForm holds basic application settings.
 *
 * @since 0.5
 */
class ConfigBasicForm extends Model
{
    /**
     * @var string name of installation
     */
    public $name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('InstallerModule.base', 'Name of your network'),
        ];
    }

}
