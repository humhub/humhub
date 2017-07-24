<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 23.07.2017
 * Time: 17:36
 */

namespace humhub\widgets;


use humhub\libs\Html;
use humhub\modules\content\models\ContentTag;

class ContentTagDropDown extends InputWidget
{
    /**
     * @var string tagClass
     */
    public $tagClass;
    public $query;
    public $contentContainer;
    public $type = true;
    public $prompt = false;
    public $promptValue = 0;

    public $items;
    private $_itemOptions = [];

    public function int() {
        if(!$this->tagClass) {
            $this->tagClass = ContentTag::class;
            // Reset default behavior inf no specific tagClass is given
            if($this->type === true) {
                $this->type = null;
            }
        }

        if(!$this->none && !$this->noneLabel) {
            $this->noneLabel = Yii::t('ContentModule.widgets_ContentTagDropDown', 'None');
        }
    }

    public function run()
    {
        $items = $this->getItems();

        if(empty($items)) {
            return;
        }

        $options = $this->getOptions();
        unset($options['id']);

        if($this->form && $this->hasModel()) {
            return $this->form->field($this->model, $this->attribute)->dropDownList($items, $options);
        } else if($this->hasModel()) {
            return Html::activeDropDownList($this->model, $this->attribute, $items, $options);
        } else {
            return Html::dropDownList($this->name, $this->value, $items, $options);
        }
    }

    public function getAttributes()
    {
        $result = [
            'class' => 'form-control',
            'options' => $this->_itemOptions
        ];

        if($this->prompt) {
            $result['prompt'] = $this->prompt;
        }

        return $result;
    }

    public function getItems()
    {
        if($this->items) {
            return $this->items;
        }

        if(!$this->query) {
            if($this->contentContainer) {
                $this->query = call_user_func($this->tagClass .'::findByContainer', $this->contentContainer);
            } elseif(!empty($this->type)){
                $type = ($this->type === true) ? $this->tagClass : $this->type;
                $this->query = call_user_func($this->tagClass .'::findByType', [$type]);
            } else {
                $this->query = call_user_func($this->tagClass .'::find');
            }
        }

        $tags = $this->items = $this->query->all();

        $result = [];
        foreach ($tags as $tag) {
            $result[$tag->id] = $tag->name;
            $this->_itemOptions[$tag->id] = [
                'data-type-color' => $tag->color
            ];
        }

        return $result;
    }

}