<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\Controller;
use humhub\services\WellKnownService;

class WellKnownController extends Controller
{
    public function actionIndex(string $file)
    {
        return WellKnownService::instance($file)->renderFile();
    }
}
