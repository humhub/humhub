<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\modules\user\models\Profile;
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
    public function getFormDefinition($definition = [])
    {
        return parent::getFormDefinition([
                    get_class($this) => [
                        'type' => 'form',
                        'title' => Yii::t('UserModule.profile', 'Text area field options'),
                        'elements' => [
                        ]
                    ]]);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $columnName = $this->profileField->internal_name;
        if (!Profile::columnExists($columnName)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(Profile::tableName(), $columnName, 'TEXT');
            Yii::$app->db->createCommand($query)->execute();
        }

        return parent::save();
    }

    /**
     * @inheritdoc
     */
    public function getFieldRules($rules = [])
    {
        $rules[] = [$this->profileField->internal_name, 'safe'];
        return parent::getFieldRules($rules);
    }

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition()
    {
        return [$this->profileField->internal_name => [
                'type' => 'markdown',
                'class' => 'form-control',
                'readonly' => (!$this->profileField->editable),
                'rows' => '3'
        ]];
    }

}
?>
