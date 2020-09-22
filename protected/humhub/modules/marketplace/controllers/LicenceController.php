<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\marketplace\components\LicenceManager;
use humhub\modules\marketplace\Module;
use Yii;

/**
 * Licence controller
 *
 * @property Module $module
 * @package humhub\modules\marketplace\controllers
 */
class LicenceController extends Controller
{

    public function actionIndex()
    {
        $model = $this->module->getLicence();

        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            LicenceManager::fetch();
            return $this->redirect(['index']);
        }

        return $this->render('index', ['model' => $model]);
    }


    public function actionRemove()
    {
        LicenceManager::remove();
        return $this->redirect(['/marketplace/licence']);
    }


}
