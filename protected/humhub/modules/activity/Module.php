<?php

namespace humhub\modules\activity;

use Exception;
use humhub\helpers\DataTypeHelper;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use Yii;

class Module extends \humhub\components\Module
{
    public $resourcesPath = 'resources';

    /**
     * @var int day to send weekly summaries on daily cron run (0 = Sunday, 6 = Saturday)
     */
    public $weeklySummaryDay = 0;

    /**
     * @var int day to send monthly summaries on daily cron run (1 = first day of the month)
     */
    public $monthlySummaryDay = 1;

    /**
     * @var bool enable mail summary feature
     * @since 1.4
     */
    public $enableMailSummaries = true;

    public static function getConfigurableActivities(): array
    {
        $activities = [];
        foreach (Yii::$app->getModules(false) as $moduleId => $module) {
            try {
                $module = Yii::$app->getModule($moduleId);
            } catch (Exception $ex) {
                Yii::error(
                    'Could not load module to determine activites! Module: ' . $moduleId . ' Error: ' . $ex->getMessage(
                    ),
                    'activity',
                );
                continue;
            }

            if ($module instanceof \humhub\components\Module) {
                foreach ($module->getActivityClasses() as $class) {
                    if (DataTypeHelper::isClassType($class, ConfigurableActivityInterface::class)) {
                        $activities[] = $class;
                    }
                }
            }
        }

        return $activities;
    }

}
