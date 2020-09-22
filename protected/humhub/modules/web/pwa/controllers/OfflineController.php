<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\web\pwa\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\ui\Module;

/**
 * Class OfflineController is responsible to generate an offline page for PWAs.
 *
 * @since 1.4
 * @property Module $module
 * @package humhub\modules\ui\controllers
 */
class OfflineController extends Controller
{
    /**
     * Allow guest access independently from guest mode setting.
     *
     * @var string
     */
    public $access = ControllerAccess::class;

    public function actionIndex()
    {
        return $this->renderPartial('@humhub/modules/web/pwa/views/offline/index');
    }
}
