<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\marketplace\Module;
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
    public function getAccessRules()
    {
        return [
            ['permissions' => ManageModules::class]
        ];
    }

    /**
     * Complete list of all modules
     */
    public function actionList()
    {
        $hasError = false;
        $message = '';

        $licenceKey = Yii::$app->request->post('licenceKey', '');

        if ($licenceKey !== '') {
            $result = HumHubAPI::request('v1/modules/registerPaid', ['licenceKey' => $licenceKey]);
            if (!isset($result['status'])) {
                $hasError = true;
                $message = 'Could not connect to HumHub API!';
            } elseif ($result['status'] == 'ok' || $result['status'] == 'created') {
                $message = 'Module licence added!';
                $licenceKey = '';
            } else {
                $hasError = true;
                $message = 'Invalid module licence key!';
            }
        }

        // Only showed purchased modules
        $onlineModules = $this->module->onlineModuleManager;
        $modules = $onlineModules->getModules(false);

        foreach ($modules as $i => $module) {
            if (!isset($module['purchased']) || !$module['purchased']) {
                unset($modules[$i]);
            }
        }

        return $this->renderAjax('list', [
            'modules' => $modules,
            'licenceKey' => $licenceKey,
            'hasError' => $hasError,
            'message' => $message,
        ]);
    }

}
