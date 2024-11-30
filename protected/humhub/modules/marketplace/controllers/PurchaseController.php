<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\marketplace\models\Module as ModelModule;
use humhub\modules\marketplace\Module;
use humhub\modules\marketplace\services\MarketplaceService;
use humhub\modules\marketplace\widgets\ModuleCard;
use Yii;

/**
 * Class PurchaseController
 *
 * @property Module $module
 * @package humhub\modules\marketplace\controllers
 */
class PurchaseController extends Controller
{
    /**
     * @var string
     */
    public $defaultAction = 'list';

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permissions' => ManageModules::class],
        ];
    }

    /**
     * Complete list of all modules
     */
    public function actionList()
    {
        $licenceKey = Yii::$app->request->post('licenceKey', '');

        $addKeyResult = MarketplaceService::addLicenceKey($licenceKey);

        // Only showed purchased modules
        $purchasedModules = $this->module->onlineModuleManager->getPurchasedModules(false);

        $html = $this->renderAjax('list', [
            'modules' => $purchasedModules,
        ] + $addKeyResult);

        if (Yii::$app->request->isGet) {
            return $html;
        }

        $purchasedModuleCards = [];
        foreach ($purchasedModules as $moduleId => $module) {
            $purchasedModuleCards[$moduleId] = ModuleCard::widget(['module' => new ModelModule($module)]);
        }

        return $this->asJson([
            'purchasedModules' => $purchasedModuleCards,
            'html' => $html,
        ]);
    }

}
