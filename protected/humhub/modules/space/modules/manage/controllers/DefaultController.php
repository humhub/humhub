<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\controllers;

use Yii;
use yii\helpers\Url;
use humhub\modules\space\modules\manage\components\Controller;
use humhub\modules\space\modules\manage\models\DeleteForm;

/**
 * Default space admin action
 *
 * @author Luke
 */
class DefaultController extends Controller
{

    /**
     * General space settings
     */
    public function actionIndex()
    {
        $space = $this->contentContainer;
        $space->scenario = 'edit';

        if ($space->load(Yii::$app->request->post()) && $space->validate() && $space->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('SpaceModule.controllers_AdminController', 'Saved'));
            return $this->redirect($space->createUrl('index'));
        }
        return $this->render('index', array('model' => $space));
    }

    /**
     * Security settings
     */
    public function actionSecurity()
    {
        $space = $this->contentContainer;
        $space->scenario = 'edit';

        if ($space->load(Yii::$app->request->post()) && $space->validate() && $space->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('SpaceModule.controllers_AdminController', 'Saved'));
            return $this->redirect($space->createUrl('security'));
        }
        return $this->render('security', array('model' => $space));
    }

    /**
     * Archives the space
     */
    public function actionArchive()
    {
        $this->ownerOnly();
        $space = $this->getSpace();
        $space->archive();
        return $this->redirect($space->createUrl('/space/manage'));
    }

    /**
     * Unarchives the space
     */
    public function actionUnarchive()
    {
        $this->ownerOnly();
        $space = $this->getSpace();
        $space->unarchive();
        return $this->redirect($space->createUrl('/space/manage'));
    }

    /**
     * Deletes this Space
     */
    public function actionDelete()
    {
        $this->ownerOnly();
        $model = new DeleteForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $this->getSpace()->delete();
            return $this->redirect(Url::home());
        }

        return $this->render('delete', array('model' => $model, 'space' => $this->getSpace()));
    }

}

?>
