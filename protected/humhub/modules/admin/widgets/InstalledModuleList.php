<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Module;
use humhub\components\Widget;
use humhub\libs\Html;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Modules displays the modules list
 *
 * @since 1.15
 * @author Luke
 */
class InstalledModuleList extends Widget
{
    public array $groups = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initDefaultGroups();

        parent::init();

        ArrayHelper::multisort($this->groups, 'sortOrder');
    }

    private function initDefaultGroups()
    {
        /* @var Module[] $modules */
        $modules = Yii::$app->moduleManager->getModules();

        $activeModules = [];
        $inactiveModules = [];
        foreach ($modules as $module) {
            if ($module->getIsEnabled()) {
                $activeModules[] = $module;
            } else {
                $inactiveModules[] = $module;
            }
        }

        $this->addGroup('active', [
            'title' => Yii::t('AdminModule.base', 'Active Modules'),
            'modules' => $activeModules,
            'count' => count($activeModules),
            'sortOrder' => 100,
        ]);

        $this->addGroup('inactive', [
            'title' => Yii::t('AdminModule.base', 'Inactive Modules'),
            'modules' => $inactiveModules,
            'count' => count($inactiveModules),
            'sortOrder' => 200,
        ]);
    }

    public function addGroup(string $groupType, array $group)
    {
        $this->groups[$groupType] = $group;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $modulesList = '';

        foreach ($this->groups as $groupType => $group) {
            if (empty($group['count'])) {
                continue;
            }

            $group['type'] = $groupType;

            $modulesList .= $this->render('installed-module-group', $group);
        }

        if ($modulesList === '') {
            return Html::tag('p', Yii::t('AdminModule.base', 'No modules installed yet. Install some to enhance the functionality!'));
        }

        return $modulesList;
    }

}
