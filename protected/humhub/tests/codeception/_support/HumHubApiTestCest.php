<?php

namespace tests\codeception\_support;

use humhub\modules\rest\Module;
use Yii;

class HumHubApiTestCest
{

    /**
     * @var string $recordModelClass Class name of the model to find a record
     */
    protected $recordModelClass;

    /**
     * @var array $recordDefinitionFunction Function name to get definitions of a record
     */
    protected $recordDefinitionFunction;

    public function _before()
    {
        $this->enableRestModule();
    }

    protected function enableRestModule()
    {
        /* @var Module $module */
        $module = Yii::$app->moduleManager->getModule('rest');
        if (!$module) {
            return false;
        }

        Yii::$app->moduleManager->enableModules(['rest']);

        $module->settings->set('enabledForAllUsers', true);
        $module->settings->set('enableBasicAuth', true);
    }

    protected function isRestModuleEnabled(): bool
    {
        $enabledModules = Yii::$app->moduleManager->getEnabledModules();
        return isset($enabledModules['rest']);
    }

    protected function getRecordDefinition(int $id, string $recordModelClass = null): array
    {
        if ($recordModelClass === null) {
            $recordModelClass = $this->recordModelClass;
        }

        $record = $recordModelClass::findOne(['id' => $id]);

        return ($record ? call_user_func($this->recordDefinitionFunction, $record) : []);
    }

    protected function getRecordDefinitions(array $ids, string $recordModelClass = null, array $recordDefinitionFunction = null): array
    {
        if ($recordModelClass === null) {
            $recordModelClass = $this->recordModelClass;
        }

        if ($recordDefinitionFunction === null) {
            $recordDefinitionFunction = $this->recordDefinitionFunction;
        }

        $recordsQuery = $recordModelClass::find()->where(['IN', 'id', $ids]);

        $records = [];
        foreach ($recordsQuery->all() as $record) {
            $records[$record->id] = call_user_func($recordDefinitionFunction, $record);
        }

        $recordDefinitions = [];
        foreach ($ids as $id) {
            $recordDefinitions[] = isset($records[$id]) ? $records[$id] : null;
        }

        return $recordDefinitions;
    }
}
