<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Widget;
use Yii;

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
    }

    private function initDefaultGroups()
    {
        $installedModules = Yii::$app->moduleManager->getModules();

        $this->addGroup('installed', [
            'title' => Yii::t('AdminModule.modules', 'Installed'),
            'modules' => Yii::$app->moduleManager->filterModules($installedModules, Yii::$app->request->get('keyword')),
            'count' => count($installedModules),
            'noModulesMessage' => Yii::t('AdminModule.base', 'No modules installed yet. Install some to enhance the functionality!'),
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
            $group['type'] = $groupType;
            $modules .= $this->render('moduleGroup', $group);
        }

        return $modules;
    }

}
