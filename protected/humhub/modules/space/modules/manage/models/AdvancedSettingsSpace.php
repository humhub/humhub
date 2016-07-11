<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\models;

use Yii;
use humhub\modules\space\models\Space;

/**
 * AdvancedSettingsSpace
 *
 * @author Luke
 */
class AdvancedSettingsSpace extends Space
{

    /**
     * Contains the form value for indexUrl setting
     * @var string|null 
     */
    public $indexUrl = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['indexUrl'], 'string'];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['edit'][] = 'indexUrl';
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['indexUrl'] = Yii::t('SpaceModule.models_Space', 'Homepage');
        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->indexUrl != null) {
            Yii::$app->getModule('space')->settings->contentContainer($this)->set('indexUrl', $this->indexUrl);
        } else {
            //Remove entry from db
            Yii::$app->getModule('space')->settings->contentContainer($this)->delete('indexUrl');
        }

        return parent::afterSave($insert, $changedAttributes);
    }

}
