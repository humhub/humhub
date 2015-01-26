<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * Index Controller shows a simple welcome page.
 *
 * @author luke
 */
class IndexController extends Controller
{

    /**
     *
     * @var String layout to use
     */
    public $layout = '_layout';

    /**
     * Index View just provides a welcome page
     */
    public function actionIndex()
    {
        
        // Render Template
        $model = new ChooseLanguageForm();
        
        if (($language = Yii::app()->request->getPreferredAvailableLanguage())) {
            Yii::app()->request->cookies['language']->value = $language;
            Yii::app()->setLanguage($model->language);
            $model->language = $language;
        }
        
        if (isset($_POST['ChooseLanguageForm'])) {
            $_POST['ChooseLanguageForm'] = Yii::app()->input->stripClean($_POST['ChooseLanguageForm']);
            $model->attributes = $_POST['ChooseLanguageForm'];
            
            if ($model->validate()) {
                Yii::app()->request->cookies['language'] = new CHttpCookie('language', $model->language);
                Yii::app()->setLanguage($model->language);
            }
        }
        $this->render('index', array('model' => $model));
    }

    /**
     * Checks if we need to call SetupController or ConfigController.
     */
    public function actionGo()
    {
        if ($this->getModule()->checkDBConnection()) {
            $this->redirect(Yii::app()->createUrl('//installer/setup/init'));
        } else {
            $this->redirect(Yii::app()->createUrl('//installer/setup/prerequisites'));
        }
    }
}
