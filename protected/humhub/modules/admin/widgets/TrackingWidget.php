<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\web\security\helpers\Security;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Yii;

/**
 * TrackingWidget adds statistic tracking code to all layouts
 *
 * @since 1.1
 * @author Luke
 */
class TrackingWidget extends \humhub\components\Widget
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        $trackingCode = Yii::$app->settings->get('trackingHtmlCode');

        if(!$trackingCode) {
            return '';
        }

        $twig = new Environment(new ArrayLoader(['trackingHtmlCode' => $trackingCode]));
        return $twig->render('trackingHtmlCode', ['nonce' => Security::getNonce()]);
    }

}
