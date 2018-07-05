<?php


namespace humhub\components;


use Yii;

class ModuleEvent extends \yii\base\Event
{
    /**
     * @var string id of the affected module
     */
    public $moduleId;

    /**
     * @var Module
     */
    public $module;

    public function init()
    {
        parent::init();
        if ($this->module) {
            $this->moduleId = $this->module->id;
        }
    }

    /**
     * @return Module|null the module instance if already installed
     */
    public function getModule()
    {
        if (!$this->module && $this->moduleId) {
            $this->module = Yii::$app->moduleManager->getModule($this->moduleId);
        }

        return $this->module;
    }
}
