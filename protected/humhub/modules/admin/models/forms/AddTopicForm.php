<?php

namespace humhub\modules\admin\models\forms;

use humhub\helpers\ArrayHelper;
use humhub\modules\topic\models\Topic;

class AddTopicForm extends Topic
{
    public $convertToGlobal;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['convertToGlobal'], 'boolean'],
        ]);
    }
}
