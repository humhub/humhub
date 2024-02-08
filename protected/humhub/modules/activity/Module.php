<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity;

use Exception;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use Yii;

/**
 * Activity BaseModule
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @since 0.5
 */
class Module extends \humhub\components\Module
{
    /**
     * @inheritdocs
     */
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


    /**
     * Returns all configurable Activities
     *
     * @return ConfigurableActivityInterface[] a list of configurable activities
     * @since 1.2
     */
    public static function getConfigurableActivities()
    {
        $activities = [];
        foreach (Yii::$app->getModules(false) as $moduleId => $module) {
            try {
                $module = Yii::$app->getModule($moduleId);
            } catch (Exception $ex) {
                Yii::error('Could not load module to determine activites! Module: ' . $moduleId . ' Error: ' . $ex->getMessage(), 'activity');
                continue;
            }

            if ($module instanceof \humhub\components\Module) {
                foreach ($module->getActivityClasses() as $class) {
                    $activity = new $class();
                    if ($activity instanceof ConfigurableActivityInterface) {
                        $activities[] = $activity;
                    }
                }
            }
        }

        return $activities;
    }

}
