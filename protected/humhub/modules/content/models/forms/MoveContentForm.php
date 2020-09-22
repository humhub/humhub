<?php

namespace humhub\modules\content\models\forms;

use Yii;
use yii\base\Model;
use humhub\modules\content\models\Content;
use humhub\modules\space\models\Space;

/**
 * Created by PhpStorm.
 * User: kingb
 * Date: 30.06.2018
 * Time: 23:25
 */

class MoveContentForm extends Model
{
    public $id;

    /**
     * @var Content
     */
    public $content;

    /**
     * @var []
     */
    public $target;

    /**
     * @var Space
     */
    protected $targetContainer;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->content = Content::findOne(['id' => $this->id]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target'], 'validateTarget'],
            [['target'], 'required']
        ];
    }

    public function attributeLabels()
    {
        return[
            'target' => Yii::t('ContentModule.base', 'Target Space')
        ];
    }

    public function validateTarget($attribute, $params, $validator)
    {
        $targetContainer = $this->getTargetContainer();

        if(!$targetContainer) {
            $this->addError($attribute, Yii::t('ContentModule.base', 'Invalid space selection.'));
        } else {
            $result = $this->content->canMove($targetContainer);
            if($result !== true) {
                $this->addError($attribute, $result);
            }
        }
    }

    public function getSearchUrl()
    {
        return $this->content->container->createUrl('/content/move/search', ['contentId' => $this->content->id]);
    }

    /**
     * @return Space|null
     */
    public function getTargetContainer()
    {
        if(!$this->targetContainer) {
            $target = isset($this->target[0]) ? $this->target[0] : null;

            if($target) {
                $this->targetContainer = Space::findOne(['guid' => $target]);
            }
        }

        return $this->targetContainer;
    }

    public function save()
    {
        if(!$this->validate()) {
            return false;
        }

        $this->content->move($this->getTargetContainer());
        return true;
    }

    public function isMovable()
    {
        return $this->content->isModelMovable();
    }
}
