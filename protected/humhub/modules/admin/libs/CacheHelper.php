<?php

namespace humhub\modules\admin\libs;

use humhub\helpers\ControllerHelper;
use humhub\modules\admin\events\FetchReloadableScriptsEvent;
use humhub\modules\admin\Module;
use Yii;
use yii\base\Component;

class CacheHelper extends Component
{
    public const EVENT_FETCH_RELOADABLE_SCRIPTS = 'fetchReloadableScripts';

    public static function getReloadableScriptUrls()
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('admin');
        $instance = new static();
        $urls = $module->defaultReloadableScripts;
        $event = new FetchReloadableScriptsEvent(['urls' => $urls]);
        $instance->trigger(static::EVENT_FETCH_RELOADABLE_SCRIPTS, $event);
        return $event->urls;
    }

    public static function flushCache(): string
    {
        $output = "Flushing asset manager ...";
        Yii::$app->assetManager->clear();

        $output .= "\nRebuilding assets ...";
        ControllerHelper::runConsoleAction('asset', [
            'protected/humhub/config/assets.php',
            'protected/humhub/config/assets-prod.php',
        ]);

        $output .= "\nFlushing cache ...";
        Yii::$app->cache->flush();

        $output .= "\nFlushing theme cache ...";
        Yii::$app->view->theme->activate();

        return $output;
    }
}
