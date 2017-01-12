<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity;

use Yii;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;

/**
 * Activity BaseModule
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @since 0.5
 */
class Module extends \humhub\components\Module
{

    /**
     * Returns all configurable Activitiess
     * 
     * @since 1.2
     * @return ConfigurableActivityInterface[] a list of configurable activities
     */
    public static function getConfigurableActivities()
    {
        $activities = [];
        foreach (Yii::$app->getModules(false) as $moduleId => $module) {
            $module = Yii::$app->getModule($moduleId);
            if ($module instanceof \humhub\components\Module) {
                foreach ($module->getActivityClasses() as $class) {
                    $activity = new $class;
                    if ($activity instanceof ConfigurableActivityInterface) {
                        $activities[] = $activity;
                    }
                }
            }
        }

        return $activities;
    }

}
