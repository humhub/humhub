<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * DataSavedWidget shows an flash message after saving
 *
 * @deprecated since 1.2 use \humhub\modules\ui\view\components\View::saved
 * @package humhub.widgets
 * @since 0.5
 * @author Andreas Strobel
 */
class DataSaved extends \yii\base\Widget
{

    /**
     * Displays / Run the Widget
     */
    public function run()
    {
        return $this->render('dataSaved', []);
    }

}
