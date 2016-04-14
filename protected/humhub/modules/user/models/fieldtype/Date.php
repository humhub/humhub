<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use Yii;
use humhub\libs\DbDateValidator;

/**
 * Date Field
 *
 * @since 1.0.0-beta.4
 */
class Date extends BaseType
{

    /**
     * @inheritdoc
     */
    public function save()
    {
        $columnName = $this->profileField->internal_name;
        if (!\humhub\modules\user\models\Profile::columnExists($columnName)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(\humhub\modules\user\models\Profile::tableName(), $columnName, 'DATE');
            Yii::$app->db->createCommand($query)->execute();
        }

        return parent::save();
    }

    /**
     * @inheritdoc
     */
    public function getFieldRules($rules = array())
    {
        $rules[] = [
            $this->profileField->internal_name,
            DbDateValidator::className(),
            'format' => Yii::$app->formatter->dateInputFormat,
            'convertToFormat' => 'Y-m-d',
        ];
        return parent::getFieldRules($rules);
    }
    
    /**
     * @inheritdoc
     */
    public function getFormDefinition($definition = array())
    {
        return count($definition) > 0 ? parent::getFormDefinition($definition) : [];
    } 

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition()
    {
        return array($this->profileField->internal_name => array(
                'type' => 'datetime',
                'format' => Yii::$app->formatter->dateInputFormat,
                'class' => 'form-control',
                'readonly' => (!$this->profileField->editable),
                'dateTimePickerOptions' => array(
                    'pickTime' => false
                )
        ));
    }

    /**
     * @inheritdoc
     */
    public function getUserValue($user, $raw = true)
    {
        $internalName = $this->profileField->internal_name;
        $date = $user->profile->$internalName;

        if ($date == "" || $date == "0000-00-00")
            return "";

        return \yii\helpers\Html::encode($date);
    }

}

?>
