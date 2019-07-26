<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\widgets;

use Yii;
use yii\helpers\Url;
use humhub\components\Widget;
use humhub\modules\ui\Module;
use humhub\modules\ui\view\components\View;

/**
 * Class MobileAppHeader
 *
 * @package humhub\modules\ui\widgets
 */
class MobileAppHeader extends Widget
{

    /**
     * Registers mobile app related Head Tags
     *
     * @param View $view
     */
    public static function registerHeadTags(View $view)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('ui');;

        $view->registerMetaTag(['name' => 'theme-color', 'content' => $module->themeColor]);
        $view->registerMetaTag(['name' => 'application-name', 'content' => Yii::$app->name]);

        // Apple/IOS headers
        // https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html
        $view->registerMetaTag(['name' => 'apple-mobile-web-app-title', 'content' => Yii::$app->name]);
        $view->registerMetaTag(['name' => 'apple-mobile-web-app-capable', 'content' => 'yes']);
        $view->registerMetaTag(['name' => 'apple-mobile-web-app-status-bar-style', 'content' => $module->themeColor]);

        $view->registerLinkTag(['rel' => 'manifest', 'href' => Url::to(['/ui/manifest'])]);

        $serviceWorkUrl = Url::to(['/ui/service-worker/index']);
        $rootPath = Yii::getAlias('@web') . '/';
        $view->registerJs(<<<JS
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('$serviceWorkUrl', { scope: '$rootPath' });
            }
JS
            , View::POS_READY, 'serviceWorkerInit');
    }

}
