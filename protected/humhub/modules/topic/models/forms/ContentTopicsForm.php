<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\models\forms;


use humhub\modules\topic\models\Topic;
use yii\base\Model;

class ContentTopicsForm extends Model
{
    public $content;

    public $topics = [];

    public function init()
    {
        $this->topics = Topic::findByContent($this->content);
    }

    public function rules()
    {
        return [
            ['topics', 'safe']
        ];
    }

    public function getContentContainer()
    {
        return $this->content->container;
    }

    public function save()
    {
        if ($this->validate()) {
            Topic::attach($this->content, $this->topics);
            return true;
        }

        return false;
    }
}
