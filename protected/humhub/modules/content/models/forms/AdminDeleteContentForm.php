<?php

namespace humhub\modules\content\models\forms;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use Yii;
use yii\base\Model;

/**
 * AdminDeleteContentForm is shown when admin deletes someone's content (e.g. post)
 */
class AdminDeleteContentForm extends Model
{
    /**
     * @var Content
     */
    public $content;

    /**
     * @var integer
     */
    public $content_id;

    /**
     * @var string
     */
    public $message;

    public function init()
    {
        if (!empty($this->content)) {
            $this->content_id = $this->content->id;
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content_id'], 'required'],
            [['message'], 'string'],
            [['content_id'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'message' => Yii::t('ContentModule.base', 'Message')
        ];
    }
}
