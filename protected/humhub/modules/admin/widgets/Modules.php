<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Widget;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Modules displays the modules list
 *
 * @since 1.11
 * @author Luke
 */
class Modules extends Widget
{
    /**
     * @var array
     */
    public $groups;

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
        $modules = Yii::$app->moduleManager->getModules();

        $activeModules = [];
        $inactiveModules = [];
        foreach ($modules as $module) {
            if ($module->isActivated) {
                $activeModules[] = $module;
            } else {
                $inactiveModules[] = $module;
            }
        }

        $this->addGroup('active', [
            'title' => Yii::t('AdminModule.modules', 'Active Modules'),
            'modules' => $activeModules,
            'count' => count($activeModules),
            'noModulesMessage' => Yii::t('AdminModule.base', 'No modules installed yet. Install some to enhance the functionality!'),
            'sortOrder' => 100,
        ]);

        $this->addGroup('inactive', [
            'title' => Yii::t('AdminModule.modules', 'Inactive Modules'),
            'modules' => $inactiveModules,
            'count' => count($inactiveModules),
            'noModulesMessage' => Yii::t('AdminModule.base', 'No modules installed yet. Install some to enhance the functionality!'),
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
        $modules = '';

        foreach ($this->groups as $groupType => $group) {
            if (empty($group['count'])) {
                continue;
            }

            $group['type'] = $groupType;
            $renderedGroup = $this->render('moduleGroup', $group);

            if (isset($group['groupTemplate'])) {
                $renderedGroup = str_replace('{group}', $renderedGroup, $group['groupTemplate']);
            }

            $modules .= $renderedGroup;
        }

        return $modules;
    }

}
