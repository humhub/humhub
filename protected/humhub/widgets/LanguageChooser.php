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

namespace humhub\widgets;

use Yii;

/**
 * LanguageChooser
 *
 * @author luke
 * @since 0.11
 */
class LanguageChooser extends \yii\base\Widget
{

    /**
     * Displays / Run the Widget
     */
    public function run()
    {
        $model = new \humhub\models\forms\ChooseLanguage();
        $model->language = Yii::$app->language;
        return $this->render('languageChooser', array('model' => $model, 'languages' => Yii::$app->i18n->getAllowedLanguages()));
    }

}
