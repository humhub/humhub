<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use Yii;

/**
 * Markdown Profile Field
 * 
 * @since 1.1
 */
class MarkdownEditor extends BaseType
{

    /**
     * @inheritdoc
     */
    public function getFormDefinition($definition = array())
    {
        return parent::getFormDefinition(array(
                    get_class($this) => array(
                        'type' => 'form',
                        'title' => Yii::t('UserModule.models_ProfileFieldTypeTextArea', 'Text area field options'),
                        'elements' => array(
                        )
        )));
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $columnName = $this->profileField->internal_name;
        if (!\humhub\modules\user\models\Profile::columnExists($columnName)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(\humhub\modules\user\models\Profile::tableName(), $columnName, 'TEXT');
            Yii::$app->db->createCommand($query)->execute();
        }

        return parent::save();
    }

    /**
     * @inheritdoc
     */
    public function getFieldRules($rules = array())
    {
        $rules[] = array($this->profileField->internal_name, 'safe');
        return parent::getFieldRules($rules);
    }

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition()
    {
        return array($this->profileField->internal_name => array(
                'type' => 'markdown',
                'class' => 'form-control',
                'rows' => '3'
        ));
    }

}
?>
