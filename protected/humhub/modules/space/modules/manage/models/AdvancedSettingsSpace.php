<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\models;

use humhub\modules\space\models\Space;
use Yii;

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
     * Contains the form value for indexGuestUrl setting
     * @var string|null 
     */
    public $indexGuestUrl = null;

    /**
     * To hide Members sidebar in the stream page
     * @var bool
     */
    public $hideMembersSidebar = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['indexUrl'], 'string'];
        $rules[] = [['indexGuestUrl'], 'string'];
        $rules[] = [['hideMembersSidebar'], 'integer'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['edit'][] = 'indexUrl';
        $scenarios['edit'][] = 'indexGuestUrl';
        $scenarios['edit'][] = 'hideMembersSidebar';

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['indexUrl'] = Yii::t('SpaceModule.base', 'Homepage');
        $labels['indexGuestUrl'] = Yii::t('SpaceModule.base', 'Homepage (Guests)');
        $labels['hideMembersSidebar'] = Yii::t('SpaceModule.base', 'Hide Members sidebar in the stream page.');

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

        if ($this->indexGuestUrl != null) {
            Yii::$app->getModule('space')->settings->contentContainer($this)->set('indexGuestUrl', $this->indexGuestUrl);
        } else {
            //Remove entry from db
            Yii::$app->getModule('space')->settings->contentContainer($this)->delete('indexGuestUrl');
        }

        if ($this->hideMembersSidebar != null) {
            Yii::$app->getModule('space')->settings->contentContainer($this)->set('hideMembersSidebar', $this->hideMembersSidebar);
        } else {
            //Remove entry from db
            Yii::$app->getModule('space')->settings->contentContainer($this)->delete('hideMembersSidebar');
        }

        return parent::afterSave($insert, $changedAttributes);
    }

}
