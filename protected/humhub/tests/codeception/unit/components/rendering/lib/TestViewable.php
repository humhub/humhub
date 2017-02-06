<?php

namespace humhub\tests\codeception\unit\components\rendering\lib;

/**
 * Description of TestViwable
 *
 * @author buddha
 */
class TestViewable extends \yii\base\Object implements \humhub\components\rendering\Viewable
{
    public $viewName;
    
    public function getViewName()
    {
        return $this->viewName;
    }

    public function getViewParams($params = [])
    {
        return \yii\helpers\ArrayHelper::merge(['title' => 'TestTitle'], $params);
    }

    public function html()
    {
        return '<h1>TestView</h1>';
    }

    public function json()
    {
        return null;
    }

    public function text()
    {
        return 'TestViewText';
    }

}
