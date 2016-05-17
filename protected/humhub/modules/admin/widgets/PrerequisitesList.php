<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\libs\SelfTest;

/**
 * PrerequisitesList widget shows all current prerequisites
 *
 * @since 1.1
 * @author Luke
 */
class PrerequisitesList extends \yii\base\Widget
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('prerequisitesList', ['checks' => SelfTest::getResults()]);
    }

    /**
     * Check there is an error
     * 
     * @return boolean
     */
    public static function hasError()
    {
        foreach (SelfTest::getResults() as $check) {
            if ($check['state'] == 'ERROR') {
                return true;
            }
        }

        return false;
    }

}
