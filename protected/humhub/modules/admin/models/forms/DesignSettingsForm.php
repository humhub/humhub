<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class DesignSettingsForm extends \yii\base\Model
{

    public $theme;
    public $paginationSize;
    public $displayName;
    public $spaceOrder;
    public $logo;
    public $dateInputDisplayFormat;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        $themes = [];
        foreach (\humhub\components\Theme::getThemes() as $theme) {
            $themes[] = $theme->name;
        }

        return array(
            array('paginationSize', 'integer', 'max' => 200, 'min' => 1),
            array('theme', 'in', 'range' => $themes),
            array(['displayName', 'spaceOrder'], 'safe'),
            array('logo', 'file', 'extensions' => ['jpg', 'png', 'jpeg'], 'maxSize' => 3 * 1024 * 1024),
            array('logo', 'dimensionValidation', 'skipOnError' => true),
            array('dateInputDisplayFormat', 'in', 'range' => ['', 'php:d/m/Y']),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'theme' => Yii::t('AdminModule.forms_DesignSettingsForm', 'Theme'),
            'paginationSize' => Yii::t('AdminModule.forms_DesignSettingsForm', 'Default pagination size (Entries per page)'),
            'displayName' => Yii::t('AdminModule.forms_DesignSettingsForm', 'Display Name (Format)'),
            'spaceOrder' => Yii::t('AdminModule.forms_DesignSettingsForm', 'Dropdown space order'),
            'logo' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Logo upload'),
            'dateInputDisplayFormat' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Date input format'),
        );
    }

    public function dimensionValidation($attribute, $param)
    {
        if (is_object($this->logo)) {
            list($width, $height) = getimagesize($this->logo->tempName);
            if ($height < 40)
                $this->addError('logo', 'Logo size should have at least 40px of height');
        }
    }

}
