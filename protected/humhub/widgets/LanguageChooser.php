<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
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

        return $this->render('languageChooser', [
            'model' => $model,
            'languages' => Yii::$app->i18n->getAllowedLanguages()
        ]);
    }

}
