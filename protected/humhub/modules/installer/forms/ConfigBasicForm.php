<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\forms;

use Yii;

/**
 * ConfigBasicForm holds basic application settings.
 *
 * @since 0.5
 */
class ConfigBasicForm extends \yii\base\Model
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
        return array(
            array('name', 'required'),
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'name' => Yii::t('InstallerModule.forms_ConfigBasicForm', 'Name of your network'),
        );
    }

}
