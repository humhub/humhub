<?php

namespace humhub\modules\user\widgets;

use Yii;
use \yii\helpers\Url;
use humhub\widgets\BasePickerField;

/**
 *
 * @package humhub.modules_core.user.widgets
 * @since 1.2
 * @author buddha
 */
class UserPickerField extends BasePickerField
{

    /**
     * @inheritdoc 
     */
    public $defaultRoute = '/user/search/json';
    
    /**
     * @inheritdoc 
     */
    public $jsWidget = 'user.picker.UserPicker';

    /**
     * @inheritdoc 
     */
    public function init() {
        $this->itemClass = \humhub\modules\user\models\User::className();
        $this->itemKey = 'guid';
    }
    
    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        if (!$this->url) {
            // provide the space id if the widget is calling from a space
            if (Yii::$app->controller->id == 'space') {
                return Url::to([$this->defaultRoute, 'space_id' => Yii::$app->controller->getSpace()->id]);
            } else {
                return Url::to([$this->defaultRoute]);
            }
        }

        return parent::getUrl();
    }

    /**
     * @inheritdoc 
     */
    protected function getData()
    {
        $result = parent::getData();
        $allowMultiple = $this->maxSelection !== 1;
        $result['placeholder'] = ($this->placeholder != null) ? $this->placeholder : Yii::t('UserModule.widgets_UserPickerField', 'Select {n,plural,=1{user} other{users}}', ['n' => ($allowMultiple) ? 2 : 1]);
        
        if($this->placeholder && !$this->placeholderMore) {
            $result['placeholder-more'] = $this->placeholder;
        } else {
            $result['placeholder-more'] = ($this->placeholderMore) ? $this->placeholderMore : Yii::t('UserModule.widgets_UserPickerField', 'Add more...');
        }
        
        $result['no-result'] = Yii::t('UserModule.widgets_UserPickerField', 'No users found for the given query.');

        if ($this->maxSelection) {
            $result['maximum-selected'] = Yii::t('UserModule.widgets_UserPickerField', 'This field only allows a maximum of {n,plural,=1{# user} other{# users}}.', ['n' => $this->maxSelection]);
        }
        return $result;
    }

    /**
     * @inheritdoc 
     */
    protected function getItemText($item)
    {
        return \yii\helpers\Html::encode($item->displayName);
    }

    /**
     * @inheritdoc 
     */
    protected function getItemImage($item)
    {
        return $item->getProfileImage()->getUrl();
    }
}
