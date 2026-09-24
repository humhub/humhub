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

    protected function enableRestModule(): void
    {
        if (!Yii::$app->moduleManager->hasModule('rest')) {
            return;
        }

        Yii::$app->moduleManager->getModule('rest')->enable();

        /* @var Module $module */
        $module = Yii::$app->moduleManager->getModule('rest');
        $module->settings->set('enabledForAllUsers', true);
        $module->settings->set('enableBasicAuth', true);
    }

    protected function isRestModuleEnabled(): bool
    {
        $enabledModules = Yii::$app->moduleManager->getEnabledModules();
        return isset($enabledModules['rest']);
    }

    protected function getRecordDefinition(int $id, ?string $recordModelClass = null): array
    {
        if ($recordModelClass === null) {
            $recordModelClass = $this->recordModelClass;
        }

        $record = $recordModelClass::findOne([$recordModelClass::tableName() . '.id' => $id]);

        return ($record ? static::normalizeDefinition(call_user_func($this->recordDefinitionFunction, $record)) : []);
    }

    /**
     * Definitions may carry `(object)` casts (fields that must serialize as `{}` instead
     * of `[]` on the wire, e.g. a comment's `extensions`). The JSON-contains comparator
     * only understands nested arrays, so expected definitions are normalized through a
     * JSON round-trip before comparison.
     */
    protected static function normalizeDefinition(array $definition): array
    {
        return json_decode(json_encode($definition), true);
    }

    protected function getRecordDefinitions(array $ids, ?string $recordModelClass = null, ?array $recordDefinitionFunction = null): array
    {
        if ($recordModelClass === null) {
            $recordModelClass = $this->recordModelClass;
        }

        if ($recordDefinitionFunction === null) {
            $recordDefinitionFunction = $this->recordDefinitionFunction;
        }

        $recordsQuery = $recordModelClass::find()->where(['IN', $recordModelClass::tableName() . '.id', $ids]);

        $records = [];
        foreach ($recordsQuery->all() as $record) {
            $records[$record->id] = static::normalizeDefinition(call_user_func($recordDefinitionFunction, $record));
        }

        $recordDefinitions = [];
        foreach ($ids as $id) {
            $recordDefinitions[] = $records[$id] ?? null;
        }

        return $recordDefinitions;
    }
}
